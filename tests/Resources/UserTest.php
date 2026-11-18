<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\User;
use Etsy\Tests\TestCase;

class UserTest extends TestCase {

  public function testGetDefaultsToTheAuthenticatedUser(): void {
    $this->respond(['user_id' => 7, 'shop_id' => 5]);
    $this->respond(['user_id' => 7, 'first_name' => 'Ada']);

    $user = User::get();

    $this->assertLastRequest('GET', '/application/users/7');
    $this->assertSame('Ada', $user->first_name);
  }

  public function testGetReturnsNullWhenThereIsNoAuthenticatedUser(): void {
    $this->respond(['error' => 'Not found'], 404);

    $this->assertNull(User::get());
    $this->assertSame(1, $this->client->requestCount(), 'Should not request /users/ with an empty ID.');
  }

  public function testGetById(): void {
    $this->respond(['user_id' => 8]);
    User::get(8);

    $this->assertLastRequest('GET', '/application/users/8');
  }
}
