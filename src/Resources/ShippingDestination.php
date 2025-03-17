<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Shipping Destination class.
 *
 * @link https://developers.etsy.com/documentation/reference#operation/createShopShippingProfileDestination
 * @author Rhys Hall hello@rhyshall.com
 */
class ShippingDestination extends Resource
{
    protected $_saveable = [
      'primary_cost',
      'secondary_cost',
      'destination_country_iso',
      'destination_region',
      'shipping_carrier_id',
      'mail_class',
      'min_delivery_days',
      'max_delivery_days'
    ];

    /**
     * Get all shipping destinations for a profile.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @param array $params
     * @return Etsy\Collection[Etsy\Resources\ShippingDestination]
     */
    public static function all(
        int $shop_id,
        int $profile_id,
        array $params = []
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}/destinations",
            "ShippingDestination",
            $params
        )->append(['shop_id' => $shop_id]);
    }

    /**
     * Create a new shipping profile destination.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @param array $data
     * @return Etsy\Resources\ShippingDestination
     */
    public static function create(
        int $shop_id,
        int $profile_id,
        array $data
    ): ?\Etsy\Resources\ShippingDestination {
        $destination = self::request(
            "POST",
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}/destinations",
            "ShippingDestination",
            $data
        );
        if ($destination) {
            $destination->shop_id = $shop_id;
        }
        return $destination;
    }

    /**
     * Update a shipping profile destination.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @param int $destination_id
     * @param array $data
     * @return Etsy\Resources\ShippingDestination
     */
    public static function update(
        int $shop_id,
        int $profile_id,
        int $destination_id,
        array $data
    ): ?\Etsy\Resources\ShippingDestination {
        $destination = self::request(
            "PUT",
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}/destinations/{$destination_id}",
            "ShippingDestination",
            $data
        );
        if ($destination) {
            $destination->shop_id = $shop_id;
        }
        return $destination;
    }

    /**
     * Deletes a shipping profile destination.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @param int $destination_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $profile_id,
        int $destination_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}/destinations/{$destination_id}",
        );
    }

    /**
     * Saves updates to the current shipping profile destination.
     *
     * @param array $data
     * @return Etsy\Resources\ShippingDestination
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\ShippingDestination {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/shipping-profiles/{$this->shipping_profile_id}/destinations/{$this->shipping_profile_destination_id}",
            $data
        );
    }

}
