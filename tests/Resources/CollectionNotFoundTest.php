<?php

namespace Etsy\Tests\Resources;

use Etsy\Collection;
use Etsy\Exception\RequestException;
use Etsy\Resources\{Listing, Shop};
use Etsy\Tests\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Every static method that returns a Collection should return an empty one on
 * a 404 (with 404_error off), rather than failing with a PHP error.
 */
class CollectionNotFoundTest extends TestCase {

  public static function collectionMethods(): array {
    $methods = [];
    foreach(glob(__DIR__ . '/../../src/Resources/*.php') as $file) {
      $class = 'Etsy\\Resources\\' . basename($file, '.php');
      foreach((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if($method->isStatic()
          && $method->getDeclaringClass()->getName() === $class
          && (string)$method->getReturnType() === Collection::class
          && $method->getName() !== 'fetchAll') {
          $methods["{$method->class}::{$method->getName()}"] = [$class, $method->getName()];
        }
      }
    }
    return $methods;
  }

  /**
   * Placeholder arguments that satisfy each parameter's type.
   */
  private function argumentsFor(ReflectionMethod $method): array {
    $args = [];
    foreach($method->getParameters() as $param) {
      if($param->isOptional()) {
        break;
      }
      $type = $param->getType();
      $names = $type instanceof ReflectionNamedType
        ? [$type->getName()]
        : array_map(fn($t) => $t->getName(), $type->getTypes());
      $args[] = match(true) {
        in_array('int', $names) => 1,
        in_array('string', $names) => 'x',
        default => [],
      };
    }
    return $args;
  }

  /**
   * @dataProvider collectionMethods
   */
  public function testReturnsEmptyCollectionOnNotFound(string $class, string $name): void {
    $this->respond(['error' => 'Not found'], 404);

    $result = $class::$name(...$this->argumentsFor(new ReflectionMethod($class, $name)));

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertSame([], $result->data);
    $this->assertSame(0, $result->count());
  }

  public function testCoversAllCollectionMethods(): void {
    $this->assertGreaterThanOrEqual(41, count(self::collectionMethods()));
  }

  public function testInstanceMethodsReturnEmptyCollectionOnNotFound(): void {
    $shop = new Shop(json_decode('{"shop_id": 5}'));
    $this->respond(['error' => 'Not found'], 404);

    $this->assertSame([], $shop->receipts()->data);
  }

  public function testPaginatingAnEmptyResultMakesNoFurtherRequests(): void {
    $this->respond(['error' => 'Not found'], 404);
    $listings = Listing::allActiveByShop(5);

    $this->assertSame([], iterator_to_array($listings->paginate()));
    $this->assertSame(1, $this->client->requestCount());
  }

  public function testNotFoundStillThrowsWhenConfigured(): void {
    $this->client->setConfig(['404_error' => true]);
    $this->respond(['error' => 'Not found'], 404);

    $this->expectException(RequestException::class);
    Listing::allActiveByShop(5);
  }
}
