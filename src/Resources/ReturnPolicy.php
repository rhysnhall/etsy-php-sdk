<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Resources\Listing;

/**
 * ReturnPolicy resource class. Represents a Etsy shop return policy.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Shop-Return-Policy
 * @author Rhys Hall hello@rhyshall.com
 */
class ReturnPolicy extends Resource
{
    /**
     * @var array
     */
    protected $_saveable = [
      'title'
    ];

    /**
     * Get all return policies for a shop.
     *
     * @param int $shop_id
     * @return Etsy\Collection[Etsy\Resources\ReturnPolicy]
     */
    public static function all(
        int $shop_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/policies/return",
            "ReturnPolicy"
        );
    }

    /**
     * Get a shop return policy.
     *
     * @param int $shop_id
     * @param int $policy_id
     * @return Etsy\Resources\ReturnPolicy
     */
    public static function get(
        int $shop_id,
        int $policy_id
    ): ?\Etsy\Resources\ReturnPolicy {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/policies/return/{$policy_id}",
            "ReturnPolicy"
        );
    }

    /**
     * Create a new shop return policy.
     *
     * @param int $shop_id
     * @param array $data
     * @return Etsy\Resources\ReturnPolicy
     */
    public static function create(
        int $shop_id,
        array $data
    ): ?\Etsy\Resources\ReturnPolicy {
        return self::request(
            "POST",
            "/application/shops/{$shop_id}/policies/return",
            "ReturnPolicy",
            $data
        );
    }

    /**
     * Update a shop return policy.
     *
     * @param int $shop_id
     * @param int $policy_id
     * @param array $data
     * @return Etsy\Resources\ReturnPolicy
     */
    public static function update(
        int $shop_id,
        int $policy_id,
        array $data
    ): ?\Etsy\Resources\ReturnPolicy {
        return self::request(
            "PUT",
            "/application/shops/{$shop_id}/policies/return/{$policy_id}",
            "ReturnPolicy",
            $data
        );
    }

    /**
     * Consolidates a return policy. Merging listings from the source to the destination policy.
     *
     * @param int $shop_id
     * @param array $data
     * @return \Etsy\Resources\ReturnPolicy
     */
    public static function consolidate(
        int $shop_id,
        array $data
    ): ?\Etsy\Resources\ReturnPolicy {
        return self::request(
            "POST",
            "/application/shops/{$shop_id}/policies/return/consolidate",
            "ReturnPolicy",
            $data
        );
    }

    /**
     * Deletes a shop return policy.
     *
     * @param int $shop_id
     * @param int $policy_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $policy_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/policies/return/{$policy_id}",
        );
    }

    /**
     * Saves updates to the current return policy.
     *
     * @param array $data
     * @return Etsy\Resources\ReturnPolicy
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\ReturnPolicy {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/policies/return/{$this->return_policy_id}",
            $data
        );
    }

    /**
     * Get all listings associated with this policy.
     *
     * @return \Etsy\Collection[\Etsy\Resources\Listing]
     */
    public function listings(): \Etsy\Collection
    {
        return Listing::allByReturnPolicy(
            $this->shop_id,
            $this->return_policy_id
        );
    }

}
