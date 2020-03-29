<?php

namespace Etsy;

use Y0lk\OAuth1\Client\Server\Etsy as Server;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use GuzzleHttp\Exception\BadResponseException;
use Etsy\Exception\{ApiException};
use Etsy\Utils\{
  PermissionScopes,
  Request as RequestUtil
};

/**
 * Etsy client class. Setups the OAuth connection
 */
class Etsy {

  const API_URL = 'https://openapi.etsy.com/v2/';

  /**
   * @var array
   */
  protected static $config;

  /**
   * @var League\OAuth1\Client\Credentials\TokenCredentials
   */
  protected $token_credentials;

  /**
   * @var \Y0lk\OAuth1\Client\Server\Etsy
   */
  protected $server;

  /**
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * @var integer|string
   */
  protected $shop;

  /**
   * @var integer|string
   */
  protected $user;

  /**
   * Client constructor method.
   *
   * @return void
   */
  public function __construct() {
    $config = static::$config;
    if(!isset($config)) {
      throw new ApiException("Etsy API config must be set.");
    }
    if(!isset($config['consumer_key']) || !isset($config['consumer_secret'])) {
      throw new ApiException("Consumer credentials missing from config file. Ensure both consumer_key and consumer_secret exist.");
    }
    $this->server = new Server([
      'identifier' => $config['consumer_key'],
      'secret' => $config['consumer_secret'],
      'scope' => isset($config['scope']) ? $config['scope'] : '',
      'callback_uri'=> isset($config['callback_uri']) ? $config['callback_uri'] : 'oob'
    ]);
    // Create the HTTP client.
    $this->client = $this->server->createHttpClient();

    if(isset($config['access_key']) && isset($config['access_secret'])) {
      $this->setTokenCredentials($config['access_key'], $config['access_secret']);
      if(isset($config['user_id'])) {
        $this->setUser($this->getUser($config['user_id']));
      }
      if(isset($config['shop_id'])) {
        $this->setShop($this->getShop($config['shop_id']));
      }
    }
  }

  /**
   * Returns an array of resource objects from the request result.
   *
   * @param object $response
   * @param string $resource
   * @return array
   */
  protected static function getCollection(\stdClass $response, string $resource) {
    if($response == false) {
      return [];
    }
    if(!isset($response->results) || empty($response->results)) {
      return [];
    }
    return static::createCollection($response->results, $resource);
  }

  /**
   * Creates an array of a single Etsy resource.
   *
   * @param array $records
   * @param string $resource
   * @return mixed
   */
  private static function createCollection(array $records, string $resource) {
    $resource = __NAMESPACE__ . "\\Resources\\{$resource}";
    return array_map(function($record) use($resource) {
      return new $resource($record);
    }, $records);
  }

  /**
   * Returns a resource object from the request result.
   *
   * @param object $response
   * @param string $resource
   * @return mixed
   */
  protected static function getResource(\stdClass $response, string $resource) {
    if($response == false) {
      return false;
    }
    if(!isset($response->results) || empty($response->results)) {
      return false;
    }
    return static::createResource($response->results[0], $resource);
  }

  /**
   * Creates a new Etsy resource.
   *
   * @param \stdClass $record
   * @param string $resource
   * @return mixed
   */
  private static function createResource(\stdClass $record, string $resource) {
    $resource = __NAMESPACE__ . "\\Resources\\{$resource}";
    return new $resource($record);
  }

  /**
   * Sets the Etsy client config property. The consumer_key and consumer_secret values are required.
   *
   * @param array $config
   * @return void
   */
  public static function setConfig($config) {
    // Convert an array of scopes into a string.
    if(isset($config['scope'])) {
      $config['scope'] = rawurlencode(
        PermissionScopes::prepare($config['scope'])
      );
    }
    static::$config = $config;
  }

  /**
   * Makes an authenticated request.
   *
   * @param string $method
   * @param string $url
   * @param array $params
   * @return \stdClass
   */
  protected function makeRequest(string $method, string $url, array $params = []) {
    // Remove invalid parameters;
    $params = RequestUtil::validateParameters($params);
    // Append the query parameters.
    if(count($params)) {
      $url .= "?".RequestUtil::prepareParameters($params);
    }
    // Get the request headers.
    $options['headers'] = $this->server->getHeaders(
      $this->token_credentials,
      $method,
      $url,
      $params
    );
    try {
      $response = $this->client->request($method, $url, $options);
    }
    catch(BadResponseException $e) {
      $response = $e->getResponse();
      $body = $response->getBody();
      $status_code = $response->getStatusCode();
      throw new \Exception("Request error. Status code: {$status_code}. Error: {$body}.");
    }
    return json_decode($response->getBody());
  }

  /**
   * Sets the Etsy application scope.
   *
   * @param array|string $scope
   * @return void
   */
  public function setScope($scope) {
    $this->server->setApplicationScope(
      rawurlencode(PermissionScopes::prepare($scope))
    );
  }

  /**
   * Sets the callback URI.
   *
   * @param string $callback_uri
   * @return void
   */
  public function setCallbackUri(string $callback_uri) {
    $this->server->setCallbackUri($callback_uri);
  }

  /**
   * Gets the authorization URL.
   *
   * @return string
   */
  public function getAuthorizationUrl() {
    return $this->server->urlAuthorization();
  }

  /**
   * Create the users token credentials.
   *
   * @param string $key
   * @param string $secret
   * @return void
   */
  public function setTokenCredentials(string $key, string $secret) {
    $token_credentials = new TokenCredentials;
    $token_credentials->setIdentifier($key);
    $token_credentials->setSecret($secret);
    $this->token_credentials = $token_credentials;
  }

  /**
   * Generate the users credentials. Save these and set as client token credentials the next time you use this class.
   *
   * @param \League\OAuth1\Client\Credentials\TemporaryCredentials $temp_creds
   * @param string $identifier
   * @param string $code
   * @return League\OAuth1\Client\Credentials\TokenCredentials
   */
  public function getTokenCredentials(TemporaryCredentials $temp_creds,
    string $identifier,
    string $code) {
    $credentials = $this->server->getTokenCredentials(
      $temp_creds,
      $identifier,
      $code
    );
    return $credentials;
  }

  /**
   * Gets temporary credentials.
   *
   * @return \League\OAuth1\Client\Credentials\TemporaryCredentials
   */
  public function getTemporaryCredentials() {
    return $this->server->getTemporaryCredentials();
  }

  /**
   * Creates a new Temporary Credentials object using the passed credentials.
   *
   * @param string $key
   * @param string $secret
   */
  public function createTemporaryCredentials(string $key, string $secret) {
    $temp = new TemporaryCredentials;
    $temp->setIdentifier($key);
    $temp->setSecret($secret);
    return $temp;
  }

  /**
   * Gets the set users ID OR the currently authenticated user's ID.
   *
   * @return integer
   */
  public function getUserId() {
    if(!isset($this->user)) {
      $user = $this->server->getUserDetails($this->token_credentials);
      return $user->uid;
    }
    return $this->user->user_id;
  }

  /**
   * Gets the set shops ID OR current authenticated users shop ID.
   *
   * @return integer
   */
  public function getShopId() {
    if(!isset($this->shop)) {
      $shop = $this->getShop();
      return $shop->shop_id;
    }
    return $this->shop->shop_id;
  }

  /**
   * Sets the user property.
   *
   * @param \Etsy\Resources\User $user
   * @return void
   */
  public function setUser($user) {
    $this->user = $user;
  }

  /**
   * Gets the currently authenticated user if no user id is passed in.
   *
   * @param mixed $user_id Both the user's ID and username are valid.
   * @return \Etsy\Resources\User
   */
  public function getUser($user_id = false) {
    if(!$user_id) {
      $user_details = $this->server->getUserDetails($this->token_credentials);
      $user_id = $user_details->uid;
    }
    $url = self::API_URL."users/{$user_id}";
    $response = $this->makeRequest('GET', $url);
    return static::getResource($response, 'User');
  }

  /**
   * Sets the shop property.
   *
   * @param \Etsy\Resources\User $user
   * @return void
   */
  public function setShop($shop) {
    $this->shop = $shop;
  }

  /**
   * Gets the currently authenticated user's shop if no user id is passed in.
   *
   * @param mixed $shop_id Both the shop's ID and name are valid.
   * @return \Etsy\Resources\Shop
   */
  public function getShop($shop_id = false) {
    if($shop_id) {
      $url = self::API_URL."shops/{$shop_id}";
    }
    else {
      $user_id = $this->getUserId();
      $url = self::API_URL."users/{$user_id}/shops";
    }
    $response = $this->makeRequest('GET', $url);
    return static::getResource($response, 'Shop');
  }

  /**
   * Gets all transactions for the currently authenticated user's shop.
   *
   * @param array $params
   * @return array(Etsy\Resources\Transaction)
   */
  public function getAllTransactions(array $params = []) {
    $url = self::API_URL."shops/{$this->getShopId()}/transactions";
    $response = $this->makeRequest('GET', $url, $params);
    return static::getCollection($response, 'Transaction');
  }

  /**
   * Gets a single transaction.
   *
   * @param integer|string $transaction_id
   * @param array $params
   */
  public function getTransaction($transaction_id, array $params = []) {
    $url = self::API_URL."transactions/{$transaction_id}";
    $response = $this->makeRequest('GET', $url, $params);
    return static::getResource($response, 'Transaction');
  }

}
