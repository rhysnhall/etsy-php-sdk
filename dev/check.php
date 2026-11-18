<?php
/**
 * Live checks against the Etsy API. Run from the repo root:
 *
 *   php dev/check.php            Read-only checks. Nothing in your shop is changed.
 *   php dev/check.php --write    Also creates a draft listing, tests JSON writes
 *                                against it, then deletes it.
 *   php dev/check.php --write --keep-draft   Same, but leaves the draft for inspection.
 *   php dev/check.php --refresh  Force an access token refresh, then exit.
 */

require __DIR__ . '/lib.php';

use Etsy\Etsy;
use Etsy\Resources\{
  User, Listing, ListingInventory, ListingPersonalization, EconomicOperator,
  ShippingProfile, ProcessingProfile, SellerTaxonomy
};

class SkipCheck extends Exception {}

$args = array_slice($argv, 1);
$write = in_array('--write', $args);
$keepDraft = in_array('--keep-draft', $args);
$results = ['PASS' => 0, 'FAIL' => 0, 'SKIP' => 0];

/**
 * Run a named check, print its outcome and return its value (null on failure/skip).
 */
function check(string $name, callable $fn) {
  global $results;
  try {
    $detail = '';
    $value = $fn($detail);
    $results['PASS']++;
    echo "  PASS  {$name}" . ($detail !== '' ? "  ({$detail})" : '') . PHP_EOL;
    return $value;
  }
  catch(SkipCheck $e) {
    $results['SKIP']++;
    echo "  SKIP  {$name}  ({$e->getMessage()})" . PHP_EOL;
  }
  catch(Throwable $e) {
    $results['FAIL']++;
    echo "  FAIL  {$name}" . PHP_EOL . "        " . get_class($e) . ": {$e->getMessage()}" . PHP_EOL;
  }
  return null;
}

function section(string $title): void {
  echo PHP_EOL . $title . PHP_EOL;
}

function ids(\Etsy\Collection $collection): array {
  return array_map(fn($listing) => (int)$listing->listing_id, $collection->data);
}

function findLeafTaxonomy(array $nodes): ?int {
  foreach($nodes as $node) {
    $children = $node->children ?? [];
    if(!count($children)) {
      return (int)$node->id;
    }
    if($leaf = findLeafTaxonomy($children)) {
      return $leaf;
    }
  }
  return null;
}

try {
  $env = devEnv();
  devRequireKeys($env);
}
catch(Throwable $e) {
  fwrite(STDERR, $e->getMessage() . PHP_EOL . "Edit dev/.env (created from dev/.env.example)." . PHP_EOL);
  exit(1);
}

if(in_array('--refresh', $args)) {
  devRefreshIfNeeded($env, true);
  echo "Access token refreshed: " . devMask($env['ETSY_ACCESS_TOKEN']) . PHP_EOL;
  exit(0);
}

echo "Etsy SDK live checks" . ($write ? " (with --write)" : " (read-only)") . PHP_EOL;

// ---------------------------------------------------------------------------
section('App keys and token');

$keysOk = check('App keystring and shared secret are valid (ping)', function(&$d) use ($env) {
  $appId = devOAuthClient($env)->ping();
  if(!$appId) {
    throw new RuntimeException('Ping did not return an application ID.');
  }
  $d = "application_id {$appId}";
  return true;
});
if(!$keysOk) {
  echo PHP_EOL . "Check ETSY_KEYSTRING and ETSY_SHARED_SECRET in dev/.env." . PHP_EOL;
  exit(1);
}

$tokenOk = check('Access token is current', function(&$d) use (&$env) {
  $d = devRefreshIfNeeded($env) ? 'refreshed' : 'still valid';
  return true;
});
if(!$tokenOk) {
  echo PHP_EOL . "Run `php -S localhost:3003 dev/auth.php` and connect your shop." . PHP_EOL;
  exit(1);
}

devEtsy($env);

$scopes = check('Token scopes', function(&$d) use ($env) {
  $scopes = Etsy::$client->scopes($env['ETSY_ACCESS_TOKEN']);
  $d = implode(' ', $scopes) ?: 'none returned';
  return $scopes;
}) ?? [];

$shopId = check('Authenticated user and shop (User::me)', function(&$d) use ($env) {
  $me = User::me();
  if(empty($me->shop_id)) {
    throw new SkipCheck('This user has no shop.');
  }
  if((string)$me->shop_id !== ($env['ETSY_SHOP_ID'] ?? '')) {
    devEnvSave(['ETSY_USER_ID' => $me->user_id, 'ETSY_SHOP_ID' => $me->shop_id]);
  }
  $d = "user {$me->user_id}, shop {$me->shop_id}";
  return (int)$me->shop_id;
});

// ---------------------------------------------------------------------------
section('Read-only checks');

$ownIds = $shopId ? check('Active listings in your shop', function(&$d) use ($shopId) {
  $ids = ids(Listing::allActiveByShop($shopId, ['limit' => 2]));
  if(count($ids) < 2) {
    throw new SkipCheck('Need at least 2 active listings; found ' . count($ids));
  }
  $d = 'using ' . implode(', ', $ids);
  return $ids;
}) : null;

$testIds = array_values(array_filter(array_map('intval', explode(',', $env['TEST_LISTING_IDS'] ?? ''))));
$testIds = count($testIds) >= 2 ? $testIds : $ownIds;

check('Listing::allByIds() with an array of IDs (PHP bracket format)', function(&$d) use ($testIds) {
  if(!$testIds) {
    throw new SkipCheck('Set TEST_LISTING_IDS or add 2 active listings.');
  }
  $returned = ids(Listing::allByIds($testIds));
  $d = count($returned) . ' of ' . count($testIds) . ' returned';
  if(count($returned) !== count($testIds)) {
    throw new RuntimeException("Only {$d}. Etsy may not accept listing_ids[0]=... format.");
  }
});

check('Listing::allByIds() with a comma separated string', function(&$d) use ($testIds) {
  if(!$testIds) {
    throw new SkipCheck('Set TEST_LISTING_IDS or add 2 active listings.');
  }
  $returned = ids(Listing::allByIds(implode(',', $testIds)));
  $d = count($returned) . ' of ' . count($testIds) . ' returned';
  if(count($returned) !== count($testIds)) {
    throw new RuntimeException("Only {$d}.");
  }
});

check('Listing::allInventoryByIds() (batch inventory)', function(&$d) use ($ownIds) {
  if(!$ownIds) {
    throw new SkipCheck('Needs 2 active listings in your shop.');
  }
  $listings = Listing::allInventoryByIds($ownIds);
  $inventory = $listings->data[0]->inventory();
  if(!$inventory instanceof ListingInventory) {
    throw new RuntimeException('No inventory attached to the returned listings.');
  }
  $d = count($listings->data) . ' listings, first has ' . count($inventory->products ?? []) . ' products';
});

check('Listing::allShippingByIds() (batch shipping)', function(&$d) use ($ownIds) {
  if(!$ownIds) {
    throw new SkipCheck('Needs 2 active listings in your shop.');
  }
  $listings = Listing::allShippingByIds($ownIds);
  $profile = $listings->data[0]->shipping_profile;
  $d = count($listings->data) . ' listings, first profile ' . ($profile->shipping_profile_id ?? 'none');
});

check('EconomicOperator::all() (GPSR)', function(&$d) use ($shopId) {
  if(!$shopId) {
    throw new SkipCheck('No shop.');
  }
  $d = count(EconomicOperator::all($shopId)->data) . ' found';
});

// ---------------------------------------------------------------------------
if(!$write) {
  section('Write checks');
  echo "  SKIP  Run with --write to test JSON writes against a temporary draft listing." . PHP_EOL;
  $results['SKIP']++;
}
else {
  section('Write checks (temporary draft listing)');
  $draftId = null;
  try {
    $missing = array_diff(['listings_w', 'listings_d'], $scopes);
    if(!$shopId || count($missing)) {
      throw new SkipCheck(!$shopId ? 'No shop.' : 'Token is missing scopes: ' . implode(' ', $missing)
        . '. Add them to ETSY_SCOPES and reconnect.');
    }

    $draftId = check('Create a draft listing (not visible to buyers)', function(&$d) use ($shopId) {
      $profile = ShippingProfile::all($shopId)->data[0] ?? null;
      if(!$profile) {
        throw new SkipCheck('Your shop needs at least one shipping profile.');
      }
      $taxonomyId = findLeafTaxonomy(SellerTaxonomy::all()->data);
      $data = [
        'quantity' => 1,
        'title' => 'SDK test draft - safe to delete',
        'description' => 'Temporary draft created by the etsy-php-sdk test environment.',
        'price' => 9.99,
        'who_made' => 'i_did',
        'when_made' => 'made_to_order',
        'is_supply' => 'false',
        'taxonomy_id' => $taxonomyId,
        'type' => 'physical',
        'shipping_profile_id' => $profile->shipping_profile_id,
      ];
      if($readiness = ProcessingProfile::all($shopId)->data[0] ?? null) {
        $data['readiness_state_id'] = $readiness->readiness_state_id;
      }
      $listing = Listing::create($shopId, $data);
      $d = "listing {$listing->listing_id}";
      return (int)$listing->listing_id;
    });

    if($draftId) {
      check('ListingInventory::update() sends JSON (nested products)', function(&$d) use ($draftId, $shopId) {
        $offering = ['price' => 12.34, 'quantity' => 3, 'is_enabled' => true];
        if($readiness = ProcessingProfile::all($shopId)->data[0] ?? null) {
          $offering['readiness_state_id'] = $readiness->readiness_state_id;
        }
        ListingInventory::update($draftId, [
          'products' => [[
            'sku' => 'SDK-TEST-1',
            'property_values' => [],
            'offerings' => [$offering],
          ]],
        ]);
        $product = ListingInventory::get($draftId)->products[0];
        $price = $product->offerings[0]->price;
        $amount = $price->amount / $price->divisor;
        if($product->sku !== 'SDK-TEST-1' || abs($amount - 12.34) > 0.001) {
          throw new RuntimeException("Unexpected inventory after update: sku {$product->sku}, price {$amount}");
        }
        $d = "sku {$product->sku}, price {$amount}";
      });

      check('ListingPersonalization::update() sends JSON', function(&$d) use ($draftId, $shopId) {
        ListingPersonalization::update($shopId, $draftId, [
          'personalization_questions' => [[
            'question_text' => 'Name to engrave',
            'question_type' => 'text_input',
            'required' => false,
            'max_allowed_characters' => 20,
          ]],
        ]);
        $questions = ListingPersonalization::get($shopId, $draftId)->personalization_questions ?? [];
        if(!count($questions)) {
          throw new RuntimeException('No personalization questions after update.');
        }
        ListingPersonalization::delete($shopId, $draftId);
        $d = count($questions) . ' question saved, then removed';
      });
    }
  }
  catch(SkipCheck $e) {
    $results['SKIP']++;
    echo "  SKIP  Write checks  ({$e->getMessage()})" . PHP_EOL;
  }
  finally {
    if($draftId && !$keepDraft) {
      check('Delete the draft listing', function(&$d) use ($draftId) {
        if(!Listing::delete($draftId)) {
          throw new RuntimeException("Could not delete draft {$draftId}. Remove it from Shop Manager.");
        }
        $d = "listing {$draftId}";
      });
    }
    else if($draftId) {
      echo "  NOTE  Draft listing {$draftId} kept (--keep-draft). Delete it from Shop Manager when done." . PHP_EOL;
    }
  }
}

echo PHP_EOL . "{$results['PASS']} passed, {$results['FAIL']} failed, {$results['SKIP']} skipped" . PHP_EOL;
exit($results['FAIL'] ? 1 : 0);
