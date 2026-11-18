<?php

namespace Etsy\Tests\Support;

use Etsy\OAuth\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * An OAuth client that never touches the network. Queue responses (or
 * exceptions) with queue(); every request sent is recorded in $history.
 */
class FakeClient extends Client {

  /** @var array<int, array{request: RequestInterface}> */
  public array $history = [];

  private MockHandler $mock;

  public function __construct(string $client_id = 'keystring', string $shared_secret = 'secret') {
    parent::__construct($client_id, $shared_secret);
    $this->mock = new MockHandler();
  }

  public function queue(...$responses): void {
    foreach($responses as $response) {
      $this->mock->append($response);
    }
  }

  public function createHttpClient() {
    $stack = HandlerStack::create($this->mock);
    $stack->push(Middleware::history($this->history));
    return new GuzzleClient(['handler' => $stack]);
  }

  public function lastRequest(): RequestInterface {
    if(!count($this->history)) {
      throw new \LogicException('No requests were sent.');
    }
    return end($this->history)['request'];
  }

  public function requestCount(): int {
    return count($this->history);
  }
}
