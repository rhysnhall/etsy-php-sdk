<?php

namespace Etsy\Tests\OAuth;

use Etsy\Exception\RequestException;
use Etsy\Tests\TestCase;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class ClientRequestTest extends TestCase {

  public function testSendsApiKeyAndBearerHeaders(): void {
    $this->respond(['ok' => true]);
    $this->client->get('/application/users/me');

    $request = $this->lastRequest();
    $this->assertSame('https://api.etsy.com/v3/application/users/me', (string)$request->getUri());
    $this->assertSame('keystring:secret', $request->getHeaderLine('x-api-key'));
    $this->assertSame('Bearer access-token', $request->getHeaderLine('Authorization'));
  }

  public function testGetParamsAreSentAsQueryString(): void {
    $this->respond();
    $this->client->get('/application/shops', ['shop_name' => 'acme', 'limit' => 5]);

    $this->assertSame('shop_name=acme&limit=5', $this->lastQuery());
  }

  public function testWriteBodiesAreFormEncodedByDefault(): void {
    $this->respond();
    $this->client->post('/application/shops/1/sections', ['title' => 'New']);

    $this->assertSame(['title' => 'New'], $this->lastForm());
  }

  public function testJsonOptionSendsJsonBody(): void {
    $this->respond();
    $this->client->put('/application/listings/1/inventory', ['products' => [['sku' => 'A']]], ['json' => true]);

    $this->assertSame(['products' => [['sku' => 'A']]], $this->lastJson());
  }

  public function testDeleteParamsAreSentAsQueryString(): void {
    $this->client->queue(new Response(204));
    $this->client->delete('/application/shops/1/sections/2', ['foo' => 'bar']);

    $this->assertSame('DELETE', $this->lastRequest()->getMethod());
    $this->assertSame('foo=bar', $this->lastQuery());
  }

  public function testResponseIncludesRequestUri(): void {
    $this->respond(['shop_id' => 1]);
    $response = $this->client->get('/application/shops/1');

    $this->assertSame('/application/shops/1', $response->uri);
  }

  public function testInvalidMethodThrows(): void {
    $this->expectException(RequestException::class);
    $this->client->head('/application/shops/1');
  }

  public function testErrorResponseThrowsWithEtsyMessageAndStatus(): void {
    $this->respond(['error' => 'Invalid listing'], 400);

    try {
      $this->client->get('/application/listings/1');
      $this->fail('Expected a RequestException.');
    }
    catch(RequestException $e) {
      $this->assertStringContainsString('[400]', $e->getMessage());
      $this->assertStringContainsString('Invalid listing', $e->getMessage());
      $this->assertSame(400, $e->getCode());
      $this->assertNotNull($e->getPrevious());
    }
  }

  public function testNonJsonErrorFallsBackToReasonPhrase(): void {
    $this->client->queue(new Response(502, [], '<html>Bad gateway</html>'));

    $this->expectException(RequestException::class);
    $this->expectExceptionMessage('Bad Gateway');
    $this->client->get('/application/listings/1');
  }

  public function testNotFoundReturnsErrorObjectByDefault(): void {
    $this->respond(['error' => 'Not found'], 404);
    $response = $this->client->get('/application/listings/1');

    $this->assertSame(404, $response->code);
    $this->assertSame('Not found', $response->error);
  }

  public function testNotFoundThrowsWhenConfigured(): void {
    $this->client->setConfig(['404_error' => true]);
    $this->respond(['error' => 'Not found'], 404);

    $this->expectException(RequestException::class);
    $this->client->get('/application/listings/1');
  }

  private function lastOptions(): array {
    return end($this->client->history)['options'];
  }

  public function testRequestsHaveDefaultTimeouts(): void {
    $this->respond();
    $this->client->get('/application/shops/1');

    $this->assertSame(10, $this->lastOptions()['connect_timeout']);
    $this->assertSame(60, $this->lastOptions()['timeout']);
  }

  public function testUploadsHaveNoOverallTimeoutByDefault(): void {
    $file = tempnam(sys_get_temp_dir(), 'etsy');
    file_put_contents($file, 'video-bytes');
    $this->respond();

    $this->client->post('/application/shops/1/listings/2/videos', ['video' => $file, 'name' => 'mug.mp4']);

    $this->assertSame(0, $this->lastOptions()['timeout']);
    $this->assertSame(10, $this->lastOptions()['connect_timeout']);
    @unlink($file);
  }

  public function testTimeoutsCanBeConfigured(): void {
    $this->client->setConfig(['timeout' => 5, 'connect_timeout' => 2]);
    $this->respond();
    $this->client->get('/application/shops/1');

    $this->assertSame(5, $this->lastOptions()['timeout']);
    $this->assertSame(2, $this->lastOptions()['connect_timeout']);
  }

  public function testTokenRequestsHaveTimeouts(): void {
    $this->respond(['access_token' => 'a', 'refresh_token' => 'r']);
    $this->client->refreshAccessToken('r');

    $this->assertSame(60, $this->lastOptions()['timeout']);
  }

  public function testConnectionFailureThrowsRequestException(): void {
    $this->client->queue(new ConnectException('Could not resolve host', new Request('GET', 'x')));

    try {
      $this->client->get('/application/listings/1');
      $this->fail('Expected a RequestException.');
    }
    catch(RequestException $e) {
      $this->assertStringContainsString('Could not resolve host', $e->getMessage());
      $this->assertInstanceOf(ConnectException::class, $e->getPrevious());
    }
  }
}
