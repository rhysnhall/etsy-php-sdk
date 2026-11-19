<?php

namespace Etsy\Tests\OAuth;

use Etsy\Exception\OAuthException;
use Etsy\OAuth\Client;
use Etsy\Tests\TestCase;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;

class ClientAuthTest extends TestCase {

  public function testRequiresClientIdAndSecret(): void {
    $this->expectException(OAuthException::class);
    new Client('keystring', ' ');
  }

  public function testAuthorizationUrlContainsPkceAndState(): void {
    $url = $this->client->getAuthorizationUrl(
      'https://example.com/callback',
      ['listings_r', 'shops_r'],
      'challenge',
      'nonce'
    );

    $this->assertStringStartsWith('https://www.etsy.com/oauth/connect/?', $url);
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $this->assertSame([
      'response_type' => 'code',
      'redirect_uri' => 'https://example.com/callback',
      'scope' => 'listings_r shops_r',
      'client_id' => 'keystring',
      'state' => 'nonce',
      'code_challenge' => 'challenge',
      'code_challenge_method' => 'S256',
    ], $query);
  }

  public function testChallengeIsBase64UrlSha256OfVerifier(): void {
    [$verifier, $challenge] = $this->client->generateChallengeCode();

    $expected = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    $this->assertSame($expected, $challenge);
    $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{43,128}$/', $verifier);
  }

  public function testNoncesAreRandomHex(): void {
    $nonce = $this->client->createNonce();

    $this->assertMatchesRegularExpression('/^[0-9a-f]{24}$/', $nonce);
    $this->assertNotSame($nonce, $this->client->createNonce());
  }

  public function testRequestAccessToken(): void {
    $this->respond(['access_token' => 'new-access', 'refresh_token' => 'new-refresh']);
    $tokens = $this->client->requestAccessToken('https://example.com/callback', 'auth-code', 'verifier');

    $this->assertSame('new-access', $tokens['access_token']);
    $this->assertSame('new-refresh', $tokens['refresh_token']);
    $this->assertSame('https://api.etsy.com/v3/public/oauth/token', (string)$this->lastRequest()->getUri());
    $this->assertSame([
      'grant_type' => 'authorization_code',
      'client_id' => 'keystring',
      'redirect_uri' => 'https://example.com/callback',
      'code' => 'auth-code',
      'code_verifier' => 'verifier',
    ], $this->lastForm());
  }

  public function testRefreshAccessToken(): void {
    $this->respond(['access_token' => 'new-access', 'refresh_token' => 'new-refresh']);
    $tokens = $this->client->refreshAccessToken('old-refresh');

    $this->assertSame('new-access', $tokens['access_token']);
    $this->assertSame([
      'grant_type' => 'refresh_token',
      'client_id' => 'keystring',
      'refresh_token' => 'old-refresh',
    ], $this->lastForm());
  }

  public function testTokenResponsesIncludeExpiry(): void {
    $this->respond(['access_token' => 'a', 'refresh_token' => 'r', 'expires_in' => 3600, 'token_type' => 'Bearer']);
    $before = time();
    $tokens = $this->client->refreshAccessToken('old-refresh');

    $this->assertSame(3600, $tokens['expires_in']);
    $this->assertGreaterThanOrEqual($before + 3600, $tokens['expires_at']);
    $this->assertLessThanOrEqual(time() + 3600, $tokens['expires_at']);
  }

  public function testTokenExpiryIsNullWhenEtsyOmitsIt(): void {
    $this->respond(['access_token' => 'a', 'refresh_token' => 'r']);
    $tokens = $this->client->requestAccessToken('https://example.com/callback', 'code', 'verifier');

    $this->assertNull($tokens['expires_in']);
    $this->assertNull($tokens['expires_at']);
  }

  public function testTokenErrorIncludesEtsyDescription(): void {
    $this->respond(['error' => 'invalid_grant', 'error_description' => 'Refresh token expired'], 400);

    try {
      $this->client->refreshAccessToken('old-refresh');
      $this->fail('Expected an OAuthException.');
    }
    catch(OAuthException $e) {
      $this->assertStringContainsString('"invalid_grant" and message "Refresh token expired"', $e->getMessage());
      $this->assertSame(400, $e->getCode());
      $this->assertNotNull($e->getPrevious());
    }
  }

  public function testTokenConnectionFailureThrowsOAuthException(): void {
    $this->client->queue(new ConnectException('timed out', new Request('POST', 'x')));

    $this->expectException(OAuthException::class);
    $this->expectExceptionMessage('timed out');
    $this->client->refreshAccessToken('old-refresh');
  }

  public function testScopes(): void {
    $this->respond(['scopes' => ['listings_r', 'shops_r']]);

    $this->assertSame(['listings_r', 'shops_r'], $this->client->scopes('access-token'));
    $this->assertSame(['token' => 'access-token'], $this->lastForm());
  }

  public function testScopesDefaultsToTheCurrentToken(): void {
    $this->client->setApiKey('current-token');
    $this->respond(['scopes' => ['listings_r']]);

    $this->assertSame(['listings_r'], $this->client->scopes());
    $this->assertSame(['token' => 'current-token'], $this->lastForm());
  }

  public function testScopesWithoutAnyTokenThrows(): void {
    $client = new \Etsy\Tests\Support\FakeClient('keystring', 'secret');

    $this->expectException(OAuthException::class);
    $client->scopes();
  }

  public function testPing(): void {
    $this->respond(['application_id' => 123]);

    $this->assertSame(123, $this->client->ping());
    $this->assertLastRequest('GET', '/application/openapi-ping');
  }
}
