<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\{EconomicOperator, Shop};
use Etsy\Tests\TestCase;

class EconomicOperatorTest extends TestCase {

  private function operator(array $overrides = []): array {
    return $overrides + [
      'economic_operator_id' => 9,
      'shop_id' => 5,
      'operator_name' => 'Acme Ltd',
      'email' => 'compliance@example.com',
      'first_line' => '1 Example Street',
      'city' => 'Berlin',
      'zip' => '10115',
      'country_iso' => 'DE',
      'phone' => '0301234567',
    ];
  }

  public function testAll(): void {
    $this->respond(['count' => 1, 'results' => [$this->operator()]]);
    $operators = EconomicOperator::all(5);

    $this->assertLastRequest('GET', '/application/shops/5/economic-operators');
    $this->assertInstanceOf(EconomicOperator::class, $operators->data[0]);
    $this->assertSame('Acme Ltd', $operators->data[0]->operator_name);
  }

  public function testGet(): void {
    $this->respond($this->operator());

    $this->assertSame(9, EconomicOperator::get(5, 9)->economic_operator_id);
    $this->assertLastRequest('GET', '/application/shops/5/economic-operators/9');
  }

  public function testCreate(): void {
    $this->respond($this->operator(), 201);
    EconomicOperator::create(5, ['operator_name' => 'Acme Ltd', 'city' => 'Berlin']);

    $this->assertLastRequest('POST', '/application/shops/5/economic-operators');
    $this->assertSame(['operator_name' => 'Acme Ltd', 'city' => 'Berlin'], $this->lastForm());
  }

  public function testUpdateUsesPatch(): void {
    $this->respond($this->operator(['city' => 'Munich']));
    EconomicOperator::update(5, 9, ['city' => 'Munich']);

    $this->assertLastRequest('PATCH', '/application/shops/5/economic-operators/9');
    $this->assertSame(['city' => 'Munich'], $this->lastForm());
  }

  public function testSaveSendsOnlyChangedFields(): void {
    $this->respond($this->operator());
    $operator = EconomicOperator::get(5, 9);
    $operator->city = 'Munich';
    $this->respond($this->operator(['city' => 'Munich']));

    $operator->save();

    $this->assertLastRequest('PATCH', '/application/shops/5/economic-operators/9');
    $this->assertSame(['city' => 'Munich'], $this->lastForm());
    $this->assertSame('Munich', $operator->city);
  }

  public function testSaveWithoutChangesSendsNothing(): void {
    $this->respond($this->operator());
    $operator = EconomicOperator::get(5, 9);
    $requests = $this->client->requestCount();

    $operator->save();

    $this->assertSame($requests, $this->client->requestCount());
  }

  public function testShopHelpers(): void {
    $shop = new Shop(json_decode('{"shop_id": 5}'));

    $this->respond(['count' => 0, 'results' => []]);
    $shop->economicOperators();
    $this->assertLastRequest('GET', '/application/shops/5/economic-operators');

    $this->respond($this->operator());
    $shop->economicOperator(9);
    $this->assertLastRequest('GET', '/application/shops/5/economic-operators/9');
  }
}
