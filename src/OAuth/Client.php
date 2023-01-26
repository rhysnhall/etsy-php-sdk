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
   * @var array
   */
  protected $request_headers = [];

  /**
   * @var array
   */
  protected $config = [];

  /**
   * Create a new instance of Client.
   *
   * @param string $client_id
   * @return void
   */
  public function __construct(
    string $client_id
  ) {
    if(is_null($client_id) || !trim($client_id)) {
      throw new OAuthException("No client ID found. A valid client ID is required.");
    }
    $this->client_id = $client_id;
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
   * Sets the users API key.
   *
   * @param string $api_key
   * @return void
   */
  public function setApiKey($api_key) {
    $this->headers = [
      'x-api-key' => $this->client_id,
      'Authorization' => "Bearer {$api_key}"
    ];
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
    if(in_array($method, ['post', 'put'])) {
      if($file = RequestUtil::prepareFile($args[1] ?? [])) {
        $opts['multipart'] = $file;
      }
      else {
        $opts['form_params'] = $args[1] ?? [];
      }
    }
    if($method == 'DELETE' && count($args[1] ?? [])) {
      $opts['query'] = $args[1];
    }
    $opts['headers'] = $this->headers;
    try {
      $client = $this->createHttpClient();
      $response = $client->{$method}(self::API_URL.$uri, $opts);
      $headers = $response->getHeaders();
      $response = json_decode($response->getBody(), false);
      if($response) {
        $response->uri = $uri;
        $response->headers = $headers;
      }
      return $response;
    }
    catch(\Exception $e) {
      $response = $e->getResponse();
      $body = json_decode($response->getBody(), false);
      $status_code = $response->getStatusCode();
      if($status_code == 404 && !($this->config['404_error'] ?? false)) {
        $response = new \stdClass;
        $response->uri = $uri;
        $response->error = "{$body->error}";
        $response->code = $status_code;
        return $response;
      }
      throw new RequestException(
        "Received HTTP status code [$status_code] with error \"{$body->error}\"."
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
      $response = $client->post(self::TOKEN_URL, ['form_params' => $params]);
      $response = json_decode($response->getBody(), false);
      return [
        'access_token' => $response->access_token,
        'refresh_token' => $response->refresh_token
      ];
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
      $response = $client->post(self::TOKEN_URL, ['form_params' => $params]);
      $response = json_decode($response->getBody(), false);
      return [
        'access_token' => $response->access_token,
        'refresh_token' => $response->refresh_token
      ];
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
      $response = $client->post(self::TOKEN_URL, ['form_params' => $params]);
      $response = json_decode($response->getBody(), false);
      return [
        'access_token' => $response->access_token,
        'refresh_token' => $response->refresh_token
      ];
    }
    catch(\Exception $e) {
      $this->handleAcessTokenError($e);
    }
  }


  /**
   * Handles OAuth errors.
   *
   * @param Exception $e
   * @return void
   * @throws Etsy\Exception\OAuthException
   */
  private function handleAcessTokenError(\Exception $e) {
    $response = $e->getResponse();
    $body = json_decode($response->getBody(), false);
    $status_code = $response->getStatusCode();
    $error_msg = "with error \"{$body->error}\"";
    if($body->error_description ?? false) {
      $error_msg .= "and message \"{$body->error_description}\"";
    }
    throw new OAuthException(
      "Received HTTP status code [$status_code] {$error_msg} when requesting access token."
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
}
