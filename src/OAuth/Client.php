<?php

namespace Etsy\OAuth;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use Etsy\Exception\{
  OAuthException,
  RequestException
};
use Etsy\Utils\{
  PermissionScopes,
  Request as RequestUtil
};

/**
 * Etsy oAuth client class.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Client {

  const CONNECT_URL = "https://www.etsy.com/oauth/connect";
  const TOKEN_URL = "https://api.etsy.com/v3/public/oauth/token";
  const API_URL = "https://api.etsy.com/v3";

  /**
   * @var string
   */
  protected $client_id;

  /**
   * The user's OAuth access token.
   *
   * @var string|null
   */
  protected $api_key;

  /**
   * @var array
   */
  protected $request_headers = [];

  /**
   * @var array
   */
  protected $config = [];

  /**
   * @var array
   */
  protected $headers = [];

  /**
   * Create a new instance of Client.
   *
   * @param string $client_id
   * @return void
   */
  public function __construct(
    string $client_id,
    string $shared_secret
  ) {
    if(is_null($client_id) || !trim($client_id) || is_null($shared_secret) || !trim($shared_secret)) {
      throw new OAuthException("A valid client ID and shared secret are required. You can find these details on the Your Apps page: https://www.etsy.com/developers/your-apps");
    }
    $this->client_id = $client_id;
    $this->headers['x-api-key'] = "{$client_id}:{$shared_secret}";
  }

  /**
   * Create a new instance of GuzzleHttp Client.
   *
   * @return GuzzleHttp\Client
   */
  public function createHttpClient() {
    return new GuzzleHttpClient();
  }

  /**
   * Sets the client config.
   *
   * @param array $config
   * @return void
   */
  public function setConfig($config) {
    $this->config = $config;
  }

  /**
   * Guzzle timeout options. Set 'timeout' and 'connect_timeout' (in seconds,
   * 0 for no limit) in the config to override the defaults. Uploads have no
   * overall timeout by default, as large videos can take a while.
   *
   * @param bool $upload
   * @return array
   */
  protected function timeouts(bool $upload = false): array {
    return [
      'connect_timeout' => $this->config['connect_timeout'] ?? 10,
      'timeout' => $this->config['timeout'] ?? ($upload ? 0 : 60),
    ];
  }

  /**
   * Sets the users API key.
   *
   * @param string $api_key
   * @return void
   */
  public function setApiKey($api_key) {
    $this->api_key = $api_key;
    $this->headers['Authorization'] = "Bearer {$api_key}";
  }

  public function __call($method, $args) {
    if(!count($args)) {
      throw new RequestException("No URI specified for this request. All requests require a URI and optional options array.");
    }
    $valid_methods = ['get', 'delete', 'patch', 'post', 'put'];
    if(!in_array($method, $valid_methods)) {
      throw new RequestException("{$method} is not a valid request method.");
    }
    $uri = $args[0];
    if($method == 'get' && count($args[1] ?? [])) {
      $uri .= "?".RequestUtil::prepareParameters($args[1]);
    }
    $request_options = $args[2] ?? [];
    if(in_array($method, ['post', 'put', 'patch'])) {
      if($request_options['json'] ?? false) {
        $opts['json'] = $args[1] ?? [];
      }
      else if($file = RequestUtil::prepareFile($args[1] ?? [])) {
        $opts['multipart'] = $file;
      }
      else {
        $opts['form_params'] = $args[1] ?? [];
      }
    }
    if($method == 'delete' && count($args[1] ?? [])) {
      $opts['query'] = $args[1];
    }
    $opts['headers'] = $this->headers;
    $opts += $this->timeouts(isset($opts['multipart']));
    try {
      $client = $this->createHttpClient();
      $response = $client->{$method}(self::API_URL.$uri, $opts);
      $response = json_decode($response->getBody(), false);
      // A few endpoints return a bare JSON list. Give it the usual shape so it
      // becomes a collection.
      if(is_array($response)) {
        $response = (object)['count' => count($response), 'results' => $response];
      }
      if($response) {
        $response->uri = $uri;
      }
      return $response;
    }
    catch(BadResponseException $e) {
      $response = $e->getResponse();
      $body = json_decode($response->getBody(), false);
      $error = $body->error ?? $response->getReasonPhrase();
      $status_code = $response->getStatusCode();
      if($status_code == 404 && !($this->config['404_error'] ?? false)) {
        $response = new \stdClass;
        $response->uri = $uri;
        $response->error = "{$error}";
        $response->code = $status_code;
        return $response;
      }
      throw new RequestException(
        "Received HTTP status code [$status_code] with error \"{$error}\".",
        $status_code,
        $e
      );
    }
    catch(\GuzzleHttp\Exception\GuzzleException $e) {
      // No response was received (e.g. connection failure or timeout).
      throw new RequestException(
        "Request to {$uri} failed: {$e->getMessage()}",
        0,
        $e
      );
    }
  }

  /**
   * Generates the Etsy authorization URL. Your user will use this URL to authorize access for your API to their Etsy account.
   *
   * @param string $redirect_uri
   * @param array $scope
   * @param string $code_challenge
   * @param string $nonce
   * @return string
   */
  public function getAuthorizationUrl(
    string $redirect_uri,
    array $scope,
    $code_challenge,
    $nonce
  ) {
    $params = [
      "response_type" => "code",
      "redirect_uri" => $redirect_uri,
      "scope" => PermissionScopes::prepare($scope),
      "client_id" => $this->client_id,
      "state" => $nonce,
      "code_challenge" => $code_challenge,
      "code_challenge_method" => "S256"
    ];
    return self::CONNECT_URL."/?".RequestUtil::prepareParameters($params);
  }

  /**
   * Requests an authorization token from the Etsy API. Also returns the refresh token.
   *
   * @param string $redirect_uri
   * @param string $code
   * @param string $verifier
   * @return array
   */
  public function requestAccessToken(
    $redirect_uri,
    $code,
    $verifier
  ) {
    $params = [
      "grant_type" => "authorization_code",
      "client_id" => $this->client_id,
      "redirect_uri" => $redirect_uri,
      'code' => $code,
      'code_verifier' => $verifier
    ];
    // Create a GuzzleHttp client.
    $client = $this->createHttpClient();
    try {
      $response = $client->post(self::TOKEN_URL, ['form_params' => $params] + $this->timeouts());
      return $this->tokenResponse($response);
    }
    catch(\Exception $e) {
      $this->handleAcessTokenError($e);
    }
  }

  /**
   * Uses the refresh token to fetch a new access token.
   *
   * @param string $refresh_token
   * @return array
   */
  public function refreshAccessToken(
    string $refresh_token
  ) {
    $params = [
      'grant_type' => 'refresh_token',
      'client_id' => $this->client_id,
      'refresh_token' => $refresh_token
    ];
    // Create a GuzzleHttp client.
    $client = $this->createHttpClient();
    try {
      $response = $client->post(self::TOKEN_URL, ['form_params' => $params] + $this->timeouts());
      return $this->tokenResponse($response);
    }
    catch(\Exception $e) {
      $this->handleAcessTokenError($e);
    }
  }

  /**
   * Exchanges a legacy OAuth 1.0 token for an OAuth 2.0 token.
   *
   * @param string $legacy_token
   * @return array
   */
  public function exchangeLegacyToken(
    string $legacy_token
  ) {
    $params = [
      "grant_type" => "token_exchange",
      "client_id" => $this->client_id,
      "legacy_token" => $legacy_token
    ];
    // Create a GuzzleHttp client.
    $client = $this->createHttpClient();
    try {
      $response = $client->post(self::TOKEN_URL, ['form_params' => $params] + $this->timeouts());
      return $this->tokenResponse($response);
    }
    catch(\Exception $e) {
      $this->handleAcessTokenError($e);
    }
  }


  /**
   * Reads the tokens from a token endpoint response. expires_in is the access
   * token's lifetime in seconds (an hour); expires_at is the Unix time it
   * expires, for storing alongside the token.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   * @return array
   */
  private function tokenResponse($response): array {
    $response = json_decode($response->getBody(), false);
    $expires_in = isset($response->expires_in) ? (int)$response->expires_in : null;
    return [
      'access_token' => $response->access_token,
      'refresh_token' => $response->refresh_token,
      'expires_in' => $expires_in,
      'expires_at' => $expires_in === null ? null : time() + $expires_in,
    ];
  }

  /**
   * Handles OAuth errors.
   *
   * @param Exception $e
   * @return void
   * @throws Etsy\Exception\OAuthException
   */
  private function handleAcessTokenError(\Exception $e) {
    if(!($e instanceof BadResponseException)) {
      throw new OAuthException(
        "Access token request failed: {$e->getMessage()}",
        0,
        $e
      );
    }
    $response = $e->getResponse();
    $body = json_decode($response->getBody(), false);
    $status_code = $response->getStatusCode();
    $error = $body->error ?? $response->getReasonPhrase();
    $error_msg = "with error \"{$error}\"";
    if($body->error_description ?? false) {
      $error_msg .= " and message \"{$body->error_description}\"";
    }
    throw new OAuthException(
      "Received HTTP status code [$status_code] {$error_msg} when requesting access token.",
      $status_code,
      $e
    );
  }

  /**
   * Generates a random string to act as a nonce in OAuth requests.
   *
   * @param int $bytes
   * @return string
   */
  public function createNonce(int $bytes = 12) {
    return bin2hex(random_bytes($bytes));
  }

  /**
   * Generates a PKCE code challenge for use in OAuth requests. The verifier will also be needed for fetching an acess token.
   *
   * @return array
   */
  public function generateChallengeCode() {
    // Create a random string.
    $string = $this->createNonce(32);
    // Base64 encode the string.
    $verifier = $this->base64Encode(
      pack("H*", $string)
    );
    // Create a SHA256 hash and base64 encode the string again.
    $code_challenge = $this->base64Encode(
      pack("H*", hash("sha256", $verifier))
    );
    return [$verifier, $code_challenge];
  }

  /**
   * URL safe base64 encoding.
   *
   * @param string $string
   * @return string
   */
  private function base64Encode($string) {
    return strtr(
      trim(
        base64_encode($string),
        "="
      ),
      "+/", "-_"
    );
  }

  /**
   * Check to confirm connectivity to the Etsy API.
   *
   * @link https://developers.etsy.com/documentation/reference#operation/ping
   * @return integer|false
   */
  public function ping() {
    $response = $this->get("/application/openapi-ping");
    return $response->application_id ?? false;
  }

  /**
   * Check the scopes granted to an access token. Defaults to the client's
   * current access token.
   *
   * @link https://developers.etsy.com/documentation/reference/#operation/tokenScopes
   * @param string|null $token
   * @return array
   * @throws OAuthException When no token is given and the client has none.
   */
  public function scopes(
    ?string $token = null
  ): array {
    $token = $token ?? $this->api_key;
    if(!$token) {
      throw new OAuthException("No access token to check. Pass one to scopes() or set one with setApiKey().");
    }
    $response = $this->post('/application/scopes', [
      'token' => $token
    ]);
    return $response->scopes ?? [];
  }
}
