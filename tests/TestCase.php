<?php

namespace Etsy\Tests;

use Etsy\Etsy;
use Etsy\Tests\Support\FakeClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\RequestInterface;

abstract class TestCase extends BaseTestCase {

  protected FakeClient $client;

  protected function setUp(): void {
    parent::setUp();
    new Etsy('keystring', 'secret', 'access-token');
    $this->client = new FakeClient('keystring', 'secret');
    $this->client->setApiKey('access-token');
    Etsy::$client = $this->client;
  }

  /**
   * Queue a JSON response.
   */
  protected function respond($body = [], int $status = 200): void {
    $this->client->queue(new Response(
      $status,
      ['Content-Type' => 'application/json'],
      is_string($body) ? $body : json_encode($body)
    ));
  }

  protected function lastRequest(): RequestInterface {
    return $this->client->lastRequest();
  }

  /**
   * Path of the last request, without the /v3 prefix.
   */
  protected function lastPath(): string {
    return preg_replace('#^/v3#', '', $this->lastRequest()->getUri()->getPath());
  }

  /**
   * Decoded query string of the last request.
   */
  protected function lastQuery(): string {
    return urldecode($this->lastRequest()->getUri()->getQuery());
  }

  /**
   * Decoded JSON body of the last request. Fails if it wasn't sent as JSON.
   */
  protected function lastJson(): array {
    $request = $this->lastRequest();
    $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
    return json_decode((string)$request->getBody(), true);
  }

  /**
   * Decoded form body of the last request. Fails if it wasn't form encoded.
   */
  protected function lastForm(): array {
    $request = $this->lastRequest();
    $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
    parse_str((string)$request->getBody(), $form);
    return $form;
  }

  protected function assertLastRequest(string $method, string $path): void {
    $this->assertSame($method, $this->lastRequest()->getMethod());
    $this->assertSame($path, $this->lastPath());
  }
}
