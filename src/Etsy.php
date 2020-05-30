<?php

namespace Etsy;

use Y0lk\OAuth1\Client\Server\Etsy as Server;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use GuzzleHttp\Exception\BadResponseException;
use Etsy\Exception\{
  ApiException,
  RequestException
};
use Etsy\Utils\{
  PermissionScopes,
  Request as RequestUtil
};

/**
 * Etsy client class. Sets up and manages the OAuth connection.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Etsy {

  const API_URL = 'https://openapi.etsy.com/v2';

  /**
   * @var array
   */
  protected static $config;

  /**
   * @var League\OAuth1\Client\Credentials\TokenCredentials
   */
  protected static $token_credentials;

  /**
   * @var \Y0lk\OAuth1\Client\Server\Etsy
   */
  protected static $server;

  /**
   * @var \GuzzleHttp\Client
   */
  protected static $client;

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
      throw new ApiException("Consumer credentials missing from config. Ensure both consumer_key and consumer_secret exist.");
    }
    static::$server = new Server([
      'identifier' => $config['consumer_key'],
      'secret' => $config['consumer_secret'],
      'scope' => isset($config['scope']) ? $config['scope'] : '',
      'callback_uri'=> isset($config['callback_uri']) ? $config['callback_uri'] : 'oob'
    ]);
    // Create the HTTP client.
    static::$client = static::$server->createHttpClient();
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
   * @return Collection
   */
  public static function getCollection($response, string $resource) {
    if(!$response) {
      return false;
    }
    // Create a new collection.
    $collection = new Collection($resource, $response->url);
    // Return an empty collection if there are no results.
    if(!isset($response->results) || empty($response->results)) {
      return $collection;
    }
    if(!empty((array)$response->pagination)) {
      // Set the next page key.
      $collection->next_page = $response->pagination->next_page;
    }
    // Create an array of resources using the results.
    $collection->data = static::createCollectionResources(
      $response->results,
      $resource
    );
    return $collection;
  }

  /**
   * Creates an array of a single Etsy resource.
   *
   * @param array $records
   * @param string $resource
   * @return mixed
   */
  public static function createCollectionResources(array $records, string $resource) {
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
  public static function getResource($response, string $resource) {
    if(!$response) {
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
  public static function createResource(\stdClass $record, string $resource) {
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
  public static function makeRequest(string $method, string $url, array $params = []) {
    $url = self::API_URL.$url;
    // Format the 'includes' paramater.
    $params = RequestUtil::formatAssociations($params);

    // Prepare a GET request.
    if($method == 'GET') {
      // Remove invalid parameters.
      $params = RequestUtil::validateParameters($params);
      // Append the query parameters to the URL.
      if(count($params)) {
        $url .= "?".RequestUtil::prepareParameters($params);
      }
    }
    // Prepare any files and empty the params variable.
    if($file = RequestUtil::prepareFile($params)) {
      $params = [];
    }
    // Get the request headers.
    $options['headers'] = static::$server->getHeaders(
      static::$token_credentials,
      $method,
      $url,
      $params
    );

    // Prepare a PUT or POST request.
    if(in_array($method, ['POST', 'PUT'])) {
      if($file) {
        $options['multipart'] = $file;
      }
      else {
        $options['form_params'] = $params;
      }
    }

    // Prepare a DELETE request.
    if($method == 'DELETE') {
      $options['query'] = $params;
    }

    try {
      $response = static::$client->request($method, $url, $options);
      $response = json_decode($response->getBody());
      $response->url = str_replace(self::API_URL, '', $url);
    }
    catch(BadResponseException $e) {
      $response = $e->getResponse();
      $body = $response->getBody();
      $status_code = $response->getStatusCode();
      // Return an empty class if no results are found or access to a particular resource is denied.
      if(
        $status_code == 404 ||
        ($status_code == 403 && $body == 'Access denied (no visible fields)')
      ) {
        $response = new \stdClass;
        $response->url = str_replace(self::API_URL, '', $url);
        $response->error = "{$body}";
        $response->code = $status_code;
        return $response;
      }
      throw new RequestException("Request error. Status code: {$status_code}. Error: {$body}.");
    }
    return $response;
  }

  /**
   * Sets the Etsy application scope.
   *
   * @param array|string $scope
   * @return void
   */
  public function setScope($scope) {
    static::$server->setApplicationScope(
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
    static::$server->setCallbackUri($callback_uri);
  }

  /**
   * Gets the authorization URL.
   *
   * @return string
   */
  public function getAuthorizationUrl() {
    return static::$server->urlAuthorization();
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
    static::$token_credentials = $token_credentials;
  }

  /**
   * Generate the users credentials. Save these and set as client token credentials the next time you use this class.
   *
   * @param \League\OAuth1\Client\Credentials\TemporaryCredentials $temp_creds
   * @param string $identifier
   * @param string $code
   * @return League\OAuth1\Client\Credentials\TokenCredentials
   */
  public function getTokenCredentials(
    TemporaryCredentials $temp_creds,
    string $identifier,
    string $code
  ) {
    $credentials = static::$server->getTokenCredentials(
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
    return static::$server->getTemporaryCredentials();
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
      $user = static::$server->getUserDetails(static::$token_credentials);
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
      $shop = self::getShop();
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
   * @param integer|string $user_id Both the user's ID and username are valid.
   * @return \Etsy\Resources\User
   */
  public function getUser($user_id = false) {
    if(!$user_id) {
      $user_details = static::$server
        ->getUserDetails(static::$token_credentials);
      $user_id = $user_details->uid;
    }
    $url = "/users/{$user_id}";
    $response = static::makeRequest('GET', $url);
    return static::getResource($response, 'User');
  }

  /**
   * Sets the shop property.
   *
   * @param \Etsy\Resources\Shop $shop
   * @return void
   */
  public function setShop($shop) {
    $this->shop = $shop;
  }

  /**
   * Gets the currently authenticated user's shop if no user id is passed in.
   *
   * @param integer|string $shop_id Both the shop's ID and name are valid.
   * @return \Etsy\Resources\Shop
   */
  public function getShop($shop_id = false) {
    if($shop_id) {
      $url = "/shops/{$shop_id}";
    }
    // If no shop ID is set then get the first shop belonging to the user.
    // Note: A user can only have one shop with Etsy.
    else {
      $user_id = self::getUserId();
      $url = "/users/{$user_id}/shops";
    }
    $response = static::makeRequest('GET', $url);
    return static::getResource($response, 'Shop');
  }

}
