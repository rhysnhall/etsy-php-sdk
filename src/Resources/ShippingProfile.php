<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Shipping Profile resource class. Represents a Shop's shipping profile in Etsy.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/Shop-ShippingProfile
 * @author Rhys Hall hello@rhyshall.com
 */
class ShippingProfile extends Resource
{
    /**
     * @var array
     */
    protected $_associations = [
      'shipping_profile_destinations' => 'ShippingDestination',
      'shipping_profile_upgrades' => "ShippingUpgrade"
    ];

    /**
     * @var array
     */
    protected $_saveable = [
      'title',
      'origin_country_iso',
      'min_processing_time',
      'max_processing_time',
      'processing_time_unit',
      'origin_postal_code'
    ];


    /**
     * Get all shipping profiles for a shop.
     *
     * @param int $shop_id
     * @return Etsy\Collection[Etsy\Resources\ShippingProfile]
     */
    public static function all(
        int $shop_id
    ): \Etsy\Collection {
        $profiles = self::request(
            "GET",
            "/application/shops/{$shop_id}/shipping-profiles",
            "ShippingProfile"
        );
        array_map(
            (function ($profile) use ($shop_id) {
                $profile->assignShopIdToProfile($shop_id);
            }),
            $profiles->data
        );
        return $profiles;
    }

    /**
     * Get a specifc shipping profile.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @return Etsy\Resources\ShippingProfile
     */
    public static function get(
        int $shop_id,
        int $profile_id
    ): ?\Etsy\Resources\ShippingProfile {
        $profile = self::request(
            "GET",
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}",
            "ShippingProfile"
        );
        if ($profile) {
            $profile->assignShopIdToProfile($shop_id);
        }
        return $profile;
    }

    /**
     * Create a new shipping profile.
     *
     * @param int $shop_id
     * @param array $data
     * @return Etsy\Resources\ShippingProfile
     */
    public static function create(
        int $shop_id,
        array $data
    ): ?\Etsy\Resources\ShippingProfile {
        $profile = self::request(
            "POST",
            "/application/shops/{$shop_id}/shipping-profiles",
            "ShippingProfile",
            $data
        );
        if ($profile) {
            $profile->assignShopIdToProfile($shop_id);
        }
        return $profile;
    }

    /**
     * Update a shipping profile.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @param array $data
     * @return Etsy\Resources\ShippingProfile
     */
    public static function update(
        int $shop_id,
        int $profile_id,
        array $data
    ): ?\Etsy\Resources\ShippingProfile {
        $profile = self::request(
            "PUT",
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}",
            "ShippingProfile",
            $data
        );
        if ($profile) {
            $profile->assignShopIdToProfile($shop_id);
        }
        return $profile;
    }

    /**
     * Deletes a shipping profile.
     *
     * @param int $shop_id
     * @param int $profile_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $profile_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/shipping-profiles/{$profile_id}",
        );
    }

    /**
     * Saves updates to the current shipping profile.
     *
     * @param array $data
     * @return Etsy\Resources\ShippingProfile
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\ShippingProfile {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/shipping-profiles/{$this->shipping_profile_id}",
            $data
        );
    }

    /**
     * Get the shipping destinations for this profile.
     *
     * @param array $params
     * @return \Etsy\Collection[Etsy\Resources\ShippingDestination]
     */
    public function destinations(
        array $params = []
    ): \Etsy\Collection {
        $destinations = ShippingDestination::all(
            $this->shop_id,
            $this->shipping_profile_id,
            $params
        );
        $this->shipping_profile_destinations = $destinations;
        return $destinations;
    }

    /**
     * Get the shipping upgrades for this profile.
     *
     * @return \Etsy\Collection[Etsy\Resources\ShippingUpgrade]
     */
    public function upgrades(): \Etsy\Collection
    {
        $upgrades = ShippingUpgrade::all(
            $this->shop_id,
            $this->shipping_profile_id
        );
        $this->shipping_profile_upgrades = $upgrades;
        return $upgrades;
    }

    /**
     * Assigns the shop ID property to the profile and associations.
     *
     * @param int $shop_id
     * @return void
     */
    private function assignShopIdToProfile(
        int $shop_id
    ) {
        $this->shop_id = $shop_id;
        array_map(
            (function ($destination) {
                $destination->shop_id = $this->shop_id;
            }),
            ($this->shipping_profile_destinations ?? [])
        );
        array_map(
            (function ($upgrade) {
                $upgrade->shop_id = $this->shop_id;
            }),
            ($this->shipping_profile_upgrades ?? [])
        );
    }

}
