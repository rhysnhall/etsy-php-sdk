<?php

namespace Etsy\Tests;

use Etsy\Etsy;
use Etsy\Resource;
use Etsy\Tests\Support\FakeClient;
use GuzzleHttp\Psr7\Response;

/**
 * Calls every public method of every resource with placeholder arguments and
 * checks each request it makes is a real Etsy API endpoint, as listed in
 * tests/fixtures/endpoints.json (rebuild it with `php dev/update-endpoints.php`).
 */
class EndpointsTest extends TestCase {

  /**
   * Methods that don't make a request of their own.
   */
  const NO_REQUEST = [
    'Listing::assignShopIdToIncludedResources',
    'ListingInventory::assignListingId',
    'ListingProduct::findOffering',
    'ListingProduct::offering',
    'ShippingProfile::assignShopIdToProfile',
  ];

  /**
   * Methods that throw by design, mapped to the exception they throw.
   */
  const THROWS = [
    'UserAddress::delete' => \Etsy\Exception\SdkException::class,
  ];

  /**
   * Values for the ID properties instance methods build paths from.
   */
  const PROPERTIES = [
    'shop_id' => 101, 'listing_id' => 102, 'user_id' => 103, 'receipt_id' => 104,
    'transaction_id' => 105, 'shipping_profile_id' => 106, 'shipping_profile_destination_id' => 107,
    'shipping_profile_upgrade_id' => 108, 'upgrade_id' => 108, 'product_id' => 109, 'offering_id' => 110,
    'property_id' => 111, 'listing_image_id' => 112, 'listing_file_id' => 113, 'video_id' => 114,
    'return_policy_id' => 115, 'shop_section_id' => 116, 'readiness_state_id' => 117,
    'economic_operator_id' => 118, 'entry_id' => 119, 'ledger_entry_id' => 119, 'user_address_id' => 120,
    'payment_id' => 121, 'holiday_id' => 122, 'taxonomy_id' => 123, 'id' => 124, 'language' => 'de',
  ];

  public function methods(): array {
    $cases = [];
    foreach(glob(dirname(__DIR__) . '/src/Resources/*.php') as $file) {
      $class = 'Etsy\\Resources\\' . basename($file, '.php');
      foreach((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
        if($method->getDeclaringClass()->getName() !== $class || str_starts_with($method->getName(), '__')) {
          continue;
        }
        $name = basename(str_replace('\\', '/', $class)) . '::' . $method->getName();
        $cases[$name] = [$class, $method->getName()];
      }
    }
    ksort($cases);
    return $cases;
  }

  /**
   * @dataProvider methods
   */
  public function testMethodRequestsARealEndpoint(string $class, string $method): void {
    $name = basename(str_replace('\\', '/', $class)) . '::' . $method;
    $reflection = new \ReflectionMethod($class, $method);
    $args = array_map([$this, 'placeholder'], $reflection->getParameters());

    // A method expects either a collection or a single resource back. Try a
    // collection response first, then a single resource.
    $requests = null;
    $errors = [];
    foreach([['count' => 0, 'results' => []], self::PROPERTIES] as $body) {
      $this->freshClient($body);
      try {
        $target = $reflection->isStatic() ? null : new $class((object)self::PROPERTIES);
        $reflection->invokeArgs($target, $args);
        $requests = $this->client->history;
        break;
      }
      catch(\Throwable $e) {
        if(isset(self::THROWS[$name]) && $e instanceof (self::THROWS[$name])) {
          $this->assertSame(0, count($this->client->history), "{$name} should throw before making a request.");
          return;
        }
        $errors[] = get_class($e) . ': ' . $e->getMessage();
      }
    }
    $this->assertNotNull($requests, "{$name} failed with either response shape:\n" . implode("\n", $errors));

    if(in_array($name, self::NO_REQUEST)) {
      $this->assertCount(0, $requests, "{$name} is listed as making no request.");
      return;
    }
    $this->assertNotEmpty($requests, "{$name} made no request. If that's intended, add it to NO_REQUEST.");
    foreach($requests as $request) {
      $call = $request['request']->getMethod() . ' ' . preg_replace('#^/v3#', '', $request['request']->getUri()->getPath());
      $this->assertTrue(self::isEndpoint($call), "{$name} requested {$call}, which isn't in Etsy's API spec.");
    }
  }

  public function testEveryEndpointPatternIsValid(): void {
    $this->assertGreaterThan(100, count(self::endpointPatterns()));
  }

  private function freshClient(array $body): void {
    $this->client = new FakeClient('keystring', 'secret');
    $this->client->setApiKey('access-token');
    Etsy::$client = $this->client;
    for($i = 0; $i < 5; $i++) {
      $this->client->queue(new Response(200, ['Content-Type' => 'application/json'], json_encode($body)));
    }
  }

  /**
   * A placeholder argument for a parameter: its default if it has one,
   * otherwise a value of the right type.
   */
  private function placeholder(\ReflectionParameter $param) {
    // save() only sends a request when there's something to save.
    if($param->getDeclaringFunction()->getName() === 'save' && $param->getName() === 'data') {
      return ['title' => 'x'];
    }
    if($param->getName() === 'url') {
      return '/application/listings/active';
    }
    if($param->isDefaultValueAvailable()) {
      return $param->getDefaultValue();
    }
    $type = $param->getType();
    $types = $type instanceof \ReflectionUnionType
      ? array_map(fn($t) => $t->getName(), $type->getTypes())
      : [$type ? $type->getName() : 'mixed'];
    $name = $param->getName();
    if(isset(self::PROPERTIES[$name])) {
      return self::PROPERTIES[$name];
    }
    switch($types[0]) {
      case 'int': return 1;
      case 'float': return 1.5;
      case 'bool': return true;
      case 'array': return [];
      case 'string': return $name === 'language' ? 'de' : 'x';
      // Uploads: an in-memory file.
      default: return 'data://text/plain,file-contents';
    }
  }

  private static function endpointPatterns(): array {
    static $patterns;
    if($patterns === null) {
      $endpoints = json_decode(file_get_contents(__DIR__ . '/fixtures/endpoints.json'), true);
      $patterns = array_map(function($endpoint) {
        return '#^' . preg_replace('#\\\\\{[^/]+?\\\\\}#', '[^/]+', preg_quote($endpoint, '#')) . '$#';
      }, array_values($endpoints));
    }
    return $patterns;
  }

  private static function isEndpoint(string $call): bool {
    foreach(self::endpointPatterns() as $pattern) {
      if(preg_match($pattern, $call)) {
        return true;
      }
    }
    return false;
  }
}
