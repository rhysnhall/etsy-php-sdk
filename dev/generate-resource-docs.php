<?php
/**
 * Regenerates docs/resources.md from the SDK source and Etsy's OpenAPI spec.
 * Run from the repository root after adding or changing resource methods:
 *
 *   php dev/generate-resource-docs.php
 *
 * Descriptions, groupings and per-method wording live in the arrays below.
 */

require __DIR__ . '/../vendor/autoload.php';

const SPEC_URL = 'https://www.etsy.com/openapi/generated/oas/3.0.0.json';
const REF_URL = 'https://developers.etsy.com/documentation/reference#operation/';
const OUTPUT = __DIR__ . '/../docs/resources.md';

$groups = [
  'Users and shops' => ['User', 'UserAddress', 'Shop', 'ShopSection', 'HolidayPreference', 'ProductionPartner', 'EconomicOperator'],
  'Listings' => ['Listing', 'ListingInventory', 'ListingProduct', 'ListingOffering', 'ListingProperty', 'ListingPersonalization', 'ListingTranslation'],
  'Listing media and files' => ['ListingImage', 'ListingVariationImage', 'ListingVideo', 'ListingFile'],
  'Orders and payments' => ['Receipt', 'Transaction', 'Shipment', 'Payment', 'LedgerEntry', 'Review'],
  'Shipping, processing and returns' => ['ShippingProfile', 'ShippingDestination', 'ShippingUpgrade', 'ShippingCarrier', 'ProcessingProfile', 'ReturnPolicy'],
  'Taxonomy' => ['SellerTaxonomy', 'SellerTaxonomyProperty', 'BuyerTaxonomy', 'BuyerTaxonomyProperty'],
];

$intro = [
  'User' => 'An Etsy user. `User::me()` returns a plain object with just `user_id` and `shop_id`; use `User::get()` for the full `User` resource.',
  'UserAddress' => 'Addresses on the authenticated user\'s profile.',
  'Shop' => 'An Etsy shop. Most shop data is reached through the instance methods on a `Shop`.',
  'ShopSection' => 'Sections that group listings within a shop.',
  'HolidayPreference' => 'Holiday processing preferences. Only available to shops in the US and Canada.',
  'ProductionPartner' => 'Production partners a shop works with.',
  'EconomicOperator' => 'GPSR economic operators (manufacturer and EU responsible person records). Use the `economic_operator_id` as `gpsr_manufacturer_id` or `gpsr_responsible_person_id` on a listing. See Etsy\'s [GPSR tutorial](https://developers.etsy.com/documentation/tutorials/gpsr-economic-operators).',
  'Listing' => 'A listing. `Listing::create()` creates a draft; publish it by setting `state` to `active`.',
  'ListingInventory' => 'The products, offerings, prices and quantities of a listing. Inventory updates are sent as JSON.',
  'ListingProduct' => 'A single product within a listing\'s inventory.',
  'ListingOffering' => 'A price and quantity offering for a product. Offerings are returned as part of a product.',
  'ListingProperty' => 'Taxonomy properties (attributes) set on a listing.',
  'ListingPersonalization' => 'Personalization questions on a listing. See Etsy\'s [personalization migration guide](https://developers.etsy.com/documentation/tutorials/personalization-migration/).',
  'ListingTranslation' => 'Translated listing titles, descriptions and tags.',
  'ListingImage' => 'Listing images. See [Uploading files](../README.md#uploading-files).',
  'ListingVariationImage' => 'Images linked to listing variations.',
  'ListingVideo' => 'Listing videos. See [Uploading files](../README.md#uploading-files).',
  'ListingFile' => 'Files attached to digital listings. See [Uploading files](../README.md#uploading-files).',
  'Receipt' => 'An order (receipt) placed with a shop.',
  'Transaction' => 'A single item sold within a receipt.',
  'Shipment' => 'A shipment on a receipt, returned in a receipt\'s `shipments`. To add tracking, use `Receipt::createShipment()` or `$receipt->addTracking()`.',
  'Payment' => 'Payments made through Etsy Payments.',
  'LedgerEntry' => 'Entries in a shop\'s payment account ledger. `all()` defaults to the past week if `min_created` and `max_created` are not given.',
  'Review' => 'Reviews left for a shop or listing.',
  'ShippingProfile' => 'Shipping profiles. Destinations and upgrades are returned as associations.',
  'ShippingDestination' => 'Destinations within a shipping profile.',
  'ShippingUpgrade' => 'Shipping upgrades (faster shipping options) within a shipping profile.',
  'ShippingCarrier' => 'Shipping carriers and mail classes available in a country.',
  'ProcessingProfile' => 'Processing profiles (Etsy calls these readiness state definitions).',
  'ReturnPolicy' => 'Shop return policies.',
  'SellerTaxonomy' => 'The seller taxonomy (listing categories). Use a node ID as a listing\'s `taxonomy_id`.',
  'SellerTaxonomyProperty' => 'Properties available for a seller taxonomy node.',
  'BuyerTaxonomy' => 'The buyer-facing taxonomy.',
  'BuyerTaxonomyProperty' => 'Properties available for a buyer taxonomy node.',
];

// Internal helpers that aren't part of the public API.
$hidden = ['fetchAll', 'assignShopIdToIncludedResources', 'assignListingId', 'assignShopIdToProfile'];

// Replaces a method's docblock summary where it is missing, vague or wrong.
$descriptions = [
  'Listing::all' => 'Search all active listings on Etsy.',
  'Listing::allByIds' => 'Get up to 100 listings by ID. Inactive listings are only returned to their owner.',
  'Listing::allInventoryByIds' => 'Get up to 100 listings with their inventory attached. Your own listings only.',
  'Listing::allShippingByIds' => 'Get up to 100 listings with their shipping profile attached. Your own listings only.',
  'Listing::allByShopSections' => 'Get all listings within one or more shop sections.',
  'Listing::inventory' => 'Get the listing inventory. Uses the inventory already attached to the listing if present.',
  'Listing::linkVideo' => 'Link an existing video to the listing.',
  'Listing::uploadImage' => 'Upload an image to the listing.',
  'Listing::uploadFile' => 'Upload a file to the listing.',
  'Listing::uploadVideo' => 'Upload a video to the listing.',
  'ListingOffering::get' => 'Get a product offering. Fetches the parent product and returns the matching offering.',
  'ListingPersonalization::get' => 'Get the personalization questions for a listing.',
  'ListingPersonalization::update' => 'Replace the personalization questions on a listing.',
  'ListingProduct::offering' => 'Get an offering on this product by ID.',
  'Receipt::save' => 'Saves updates to the current receipt.',
  'Review::all' => 'Get all reviews for a shop.',
  'Review::allByListing' => 'Get all reviews for a listing.',
  'SellerTaxonomy::all' => 'Get the full seller taxonomy tree.',
  'ShippingCarrier::all' => 'Get shipping carriers and mail classes for a country (ISO code).',
  'Shop::all' => 'Search for shops by name.',
  'Shipment::create' => 'Deprecated. Returns receipt data in a `Shipment`; use `Receipt::createShipment()` or `$receipt->addTracking()`.',
  'Receipt::createShipment' => 'Submit tracking for a receipt. Etsy emails the buyer. Returns the updated receipt.',
  'Receipt::addTracking' => 'Submit tracking for this receipt and update it with Etsy\'s response. Etsy emails the buyer.',
  'Receipt::transactions' => 'Get the receipt\'s transactions. Uses those included with the receipt if present.',
  'Listing::personalization' => 'Get the listing personalization. Uses personalization included with the listing if present.',
  'ListingPersonalization::save' => 'Saves updates to the current listing personalization. Pass `false` as the second argument for legacy (single question) mode.',
  'Shop::featuedListings' => 'Deprecated. Use `featuredListings()`.',
  'User::get' => 'Get a user. Defaults to the authenticated user.',
  'User::me' => 'Get the `user_id` and `shop_id` of the authenticated user, as a plain object.',
  'UserAddress::delete' => 'Deprecated. Etsy has removed this endpoint; throws `SdkException`.',
];

// ---------------------------------------------------------------------------

function normalisePath(string $path): string {
  $path = preg_replace('#^/v3#', '', explode('?', $path)[0]);
  return preg_replace('/\{[^}]*\}/', '{}', $path);
}

function loadOperations(): array {
  $json = @file_get_contents(SPEC_URL);
  if($json === false) {
    fwrite(STDERR, "Could not download Etsy's OpenAPI spec from " . SPEC_URL . PHP_EOL);
    exit(1);
  }
  $spec = json_decode($json, true);
  $ops = [];
  foreach($spec['paths'] as $path => $item) {
    foreach($item as $method => $op) {
      if(!in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) continue;
      $scopes = [];
      foreach($op['security'] ?? [] as $requirement) {
        $scopes = array_merge($scopes, $requirement['oauth2'] ?? []);
      }
      sort($scopes);
      $ops[strtoupper($method) . ' ' . normalisePath($path)] = [$op['operationId'], array_unique($scopes)];
    }
  }
  return $ops;
}

function summary(string|false $doc): string {
  if($doc && preg_match('/\*\s+([^@\s*][^\n]*)/', $doc, $m)) {
    return trim($m[1]);
  }
  return '';
}

function typeName(?ReflectionType $type): string {
  if(!$type) return '';
  $name = str_replace(['Etsy\\Resources\\', 'Etsy\\'], '', (string)$type);
  return str_starts_with($name, '?') ? substr($name, 1) . '|null' : $name;
}

function signature(string $class, ReflectionMethod $method): string {
  $params = array_map(function(ReflectionParameter $p) {
    $type = $p->getType() ? str_replace(['Etsy\\Resources\\', 'Etsy\\'], '', (string)$p->getType()) . ' ' : '';
    $s = $type . '$' . $p->getName();
    if($p->isDefaultValueAvailable()) {
      $value = $p->getDefaultValue();
      $s .= ' = ' . match(true) {
        $value === null => 'null',
        $value === [] => '[]',
        is_bool($value) => $value ? 'true' : 'false',
        default => var_export($value, true),
      };
    }
    return $s;
  }, $method->getParameters());
  $prefix = $method->isStatic() ? "{$class}::" : '$' . lcfirst($class) . '->';
  return $prefix . $method->getName() . '(' . implode(', ', $params) . ')';
}

/**
 * Endpoints a method calls directly, and SDK methods it delegates to.
 */
function calls(ReflectionMethod $method, array $source): array {
  $body = implode('', array_slice($source, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));
  $endpoints = [];
  preg_match_all('/(?:request(?:Collection)?\(\s*["\'](\w+)["\'],\s*|fetchAll\(\s*|deleteRequest\(\s*|updateRequest\(\s*)"(\/application[^"]*)"(?:[^;]*?"(PATCH|POST|PUT)")?/s', $body, $matches, PREG_SET_ORDER);
  foreach($matches as $m) {
    if($m[1]) $verb = $m[1];
    else if(str_contains($m[0], 'fetchAll')) $verb = 'GET';
    else if(str_contains($m[0], 'deleteRequest')) $verb = 'DELETE';
    else $verb = ($m[3] ?? '') ?: 'PUT';
    $endpoints[] = $verb . ' ' . preg_replace('/\{\$(this->)?(\w+)\}/', '{$2}', explode('?', $m[2])[0]);
  }
  preg_match_all('/client->(get|post|put|patch|delete)\(\s*"(\/application[^"]*)"/', $body, $matches, PREG_SET_ORDER);
  foreach($matches as $m) {
    $endpoints[] = strtoupper($m[1]) . ' ' . $m[2];
  }
  preg_match_all('/(\w+)::(\w+)\(/', $body, $matches, PREG_SET_ORDER);
  $delegates = [];
  foreach($matches as $m) {
    if(!in_array($m[1], ['self', 'parent', 'static', 'Etsy'])) $delegates[] = "{$m[1]}::{$m[2]}()";
  }
  preg_match_all('/\$this->(\w+)\(/', $body, $matches);
  foreach($matches[1] as $name) {
    if($name !== $method->getName() && !in_array($name, ['request', 'updateRequest', 'getSaveData'])) {
      $delegates[] = '$' . lcfirst($method->getDeclaringClass()->getShortName()) . "->{$name}()";
    }
  }
  return [$endpoints, array_values(array_unique($delegates))];
}

function endpointCells(array $endpoints, array $ops): array {
  return array_map(function($endpoint) use ($ops) {
    [$verb, $path] = explode(' ', $endpoint, 2);
    $short = str_replace('/application', '', $path);
    $op = $ops[$verb . ' ' . normalisePath($path)] ?? null;
    if(!$op) {
      fwrite(STDERR, "Warning: {$endpoint} is not in Etsy's spec." . PHP_EOL);
      return ["`{$verb} {$short}`", ''];
    }
    $scopes = $op[1] ? implode(', ', array_map(fn($s) => "`{$s}`", $op[1])) : 'none';
    return ["[`{$verb} {$short}`](" . REF_URL . $op[0] . ')', $scopes];
  }, $endpoints);
}

function cell(string $text): string {
  // Pipes inside code spans still split GitHub table cells, so escape them.
  return preg_replace_callback('/`[^`]*`/', fn($m) => str_replace('|', '\\|', $m[0]), $text);
}

function sentence(string $text): string {
  $text = trim($text);
  return $text !== '' && !str_ends_with($text, '.') ? "{$text}." : $text;
}

// ---------------------------------------------------------------------------

$ops = loadOperations();

$classes = array_map(fn($f) => basename($f, '.php'), glob(__DIR__ . '/../src/Resources/*.php'));
$listed = array_merge(...array_values($groups));
if($missing = array_diff($classes, $listed)) {
  fwrite(STDERR, 'Add these resources to $groups and $intro: ' . implode(', ', $missing) . PHP_EOL);
  exit(1);
}

$lines = [
  '# Resource reference',
  '',
  'Every resource class in `Etsy\\Resources`, with the Etsy endpoint each method calls and the OAuth scope it needs.',
  'Endpoints link to Etsy\'s API reference, which lists the parameters and fields each one accepts.',
  '',
  'Static methods (`Listing::get()`) make a request directly. Instance methods (`$listing->images()`) are shortcuts',
  'on a resource you already have; most call a static method on a related resource.',
  'For how resources, collections and saving work, see the [README](../README.md).',
  '',
  '> This file is generated by `php dev/generate-resource-docs.php`. Edit descriptions in that script rather than here.',
  '',
  '## Contents',
  '',
];
foreach($groups as $group => $members) {
  $lines[] = "- **{$group}:** " . implode(', ', array_map(fn($c) => "[{$c}](#" . strtolower($c) . ')', $members));
}

foreach($groups as $group => $members) {
  $lines[] = '';
  $lines[] = "## {$group}";
  foreach($members as $class) {
    $ref = new ReflectionClass("Etsy\\Resources\\{$class}");
    $source = file($ref->getFileName());
    $defaults = $ref->getDefaultProperties();
    $static = $instance = [];

    foreach($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
      if($method->getDeclaringClass()->getName() !== $ref->getName() || in_array($method->getName(), $hidden)) continue;
      $description = sentence($descriptions["{$class}::{$method->getName()}"] ?? summary($method->getDocComment()));
      $returns = typeName($method->getReturnType()) ?: 'void';
      [$endpoints, $delegates] = calls($method, $source);
      $cells = endpointCells($endpoints, $ops);
      $sig = '`' . signature($class, $method) . "`<br>{$description}";
      if($method->isStatic()) {
        $static[] = cell("| {$sig} | `{$returns}` | " . implode('<br>', array_column($cells, 0)) . ' | ' . implode('<br>', array_column($cells, 1)) . ' |');
      }
      else {
        $target = $cells ? implode('<br>', array_column($cells, 0)) : (implode('<br>', array_map(fn($d) => "`{$d}`", $delegates)) ?: 'No request');
        $instance[] = cell("| {$sig} | `{$returns}` | {$target} |");
      }
    }

    array_push($lines, '', "### {$class}", '', $intro[$class], '');
    if($static) {
      array_push($lines, '| Method | Returns | Etsy endpoint | Scope |', '|---|---|---|---|', ...$static);
    }
    if($instance) {
      array_push($lines, '', '| Instance method | Returns | Calls |', '|---|---|---|', ...$instance);
    }
    if($saveable = $defaults['_saveable'] ?? []) {
      array_push($lines, '', 'Fields sent by `save()`: ' . implode(', ', array_map(fn($f) => "`{$f}`", $saveable)) . '.');
    }
    if($associations = $defaults['_associations'] ?? []) {
      $list = [];
      foreach($associations as $key => $resource) $list[] = "`{$key}` ({$resource})";
      array_push($lines, '', 'Associations: ' . implode(', ', $list) . '.');
    }
  }
}

file_put_contents(OUTPUT, implode("\n", $lines) . "\n");
echo 'Wrote docs/resources.md (' . count($lines) . " lines)\n";
