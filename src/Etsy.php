<?php

namespace Etsy;

use Etsy\OAuth\Client;
use Etsy\Exception\ApiException;

class Etsy
{
    /**
     * @var string
     */
    protected $api_key;

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var Etsy\OAuth\Client
     */
    public static $client;

    /**
     * @var integer|string
     */
    protected $user;

    public function __construct(
        string $client_id,
        ?string $api_key = null,
        array $config = []
    ) {
        $this->client_id = $client_id;
        $this->api_key = $api_key;
        static::$client = new Client($client_id);
        if ($api_key) {
            static::$client->setApiKey($api_key);
        }
        static::$client->setConfig($config);
    }

    /**
     * Returns a resource object from the request result.
     *
     * @param object $response
     * @param string $resource
     * @return mixed
     */
    public static function getResource(
        $response,
        string $resource
    ) {
        if (!$response || ($response->error ?? false)) {
            return null;
        }
        if (isset($response->results)) {
            return static::createCollection($response, $resource);
        }
        return static::createResource($response, $resource);
    }

    /**
     *
     */
    public static function createCollection(
        $response,
        string $resource
    ) {
        $collection = new Collection($resource, $response->uri);
        if (isset($response->count)) {
            $collection->count = $response->count;
        }
        if (!count($response->results) || !isset($response->results)) {
            return $collection;
        }
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
    public static function createCollectionResources(array $records, string $resource)
    {
        $resource = __NAMESPACE__ . "\\Resources\\{$resource}";
        return array_map(function ($record) use ($resource) {
            return new $resource($record);
        }, $records);
    }

    /**
     * Creates a new Etsy resource.
     *
     * @param json $record
     * @param string $resource
     * @return mixed
     */
    public static function createResource(
        $record,
        string $resource
    ) {
        $resource = __NAMESPACE__ . "\\Resources\\{$resource}";
        return new $resource($record);
    }

    /**
     * Check the permission scopes for the current Etsy user.
     *
     * @return array
     */
    public function scopes(): array
    {
        return static::$client->scopes($this->api_key);
    }

}
