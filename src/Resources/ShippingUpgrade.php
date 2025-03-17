<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Shipping Upgrade class.
 *
 * @link https://developers.etsy.com/documentation/reference#operation/createShopShippingProfileUpgrade
 * @author Rhys Hall hello@rhyshall.com
 */
class ShippingUpgrade extends Resource
{
    protected $_saveable = [
      'upgrade_name',
      'type',
      'price',
      'destination_region',
      'secondary_price',
      'shipping_carrier_id',
      'mail_class',
      'min_delivery_days',
      'max_delivery_days'
    ];

    /**
     * Get all shipping upgrades for a profile.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @return Etsy\Collection[Etsy\Resources\ShippingUpgrade]
     */
    public static function all(
        int $shop_id,
        int $profile_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}/upgrades",
            "ShippingUpgrade"
        )->append(['shop_id' => $shop_id]);
    }

    /**
     * Create a new shipping profile upgrade.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @param array $data
     * @return Etsy\Resources\ShippingUpgrade
     */
    public static function create(
        int $shop_id,
        int $profile_id,
        array $data
    ): ?\Etsy\Resources\ShippingUpgrade {
        $upgrade = self::request(
            "POST",
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}/upgrades",
            "ShippingUpgrade",
            $data
        );
        if ($upgrade) {
            $upgrade->shop_id = $shop_id;
        }
        return $upgrade;
    }

    /**
     * Update a shipping profile upgrade.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @param int $upgrade_id
     * @param array $data
     * @return Etsy\Resources\ShippingUpgrade
     */
    public static function update(
        int $shop_id,
        int $profile_id,
        int $upgrade_id,
        array $data
    ): ?\Etsy\Resources\ShippingUpgrade {
        $upgrade = self::request(
            "PUT",
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}/upgrades/{$upgrade_id}",
            "ShippingUpgrade",
            $data
        );
        if ($upgrade) {
            $upgrade->shop_id = $shop_id;
        }
        return $upgrade;
    }

    /**
     * Deletes a shipping profile upgrade.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @param int $upgrade_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $profile_id,
        int $upgrade_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}/upgrades/{$upgrade_id}",
        );
    }

    /**
     * Saves updates to the current shipping profile upgrade.
     *
     * @param array $data
     * @return Etsy\Resources\ShippingUpgrade
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\ShippingUpgrade {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/shipping-profiles/{$this->shipping_profile_id}/upgrades/{$this->upgrade_id}",
            $data
        );
    }
}
