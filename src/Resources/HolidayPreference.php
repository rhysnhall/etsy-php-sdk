<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Resources\Listing;

/**
 * HolidayPreference resource class.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/Shop-HolidayPreferences
 * @author Rhys Hall hello@rhyshall.com
 */
class HolidayPreference extends Resource
{
    /**
     * @var array
     */
    protected $_saveable = [
      'is_working'
    ];

    /**
     * Get all holiday preferences for a shop.
     *
     * @param int $shop_id
     * @return Etsy\Collection[Etsy\Resources\HolidayPreference]
     */
    public static function all(
        int $shop_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/holiday-preferences",
            "HolidayPreference"
        );
    }

    /**
     * Update a shop holiday preference.
     *
     * @param int $shop_id
     * @param int $holiday_id
     * @param array $data
     * @return Etsy\Resources\HolidayPreference
     */
    public static function update(
        int $shop_id,
        int $holiday_id,
        array $data
    ): ?\Etsy\Resources\HolidayPreference {
        return self::request(
            "PUT",
            "/application/shops/{$shop_id}/holiday-preferences/{$holiday_id}",
            "HolidayPreference",
            $data
        );
    }

    /**
     * Saves updates to the current holiday preference.
     *
     * @param array $data
     * @return Etsy\Resources\HolidayPreference
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\HolidayPreference {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/holiday-preferences/{$this->holiday_id}",
            $data
        );
    }

}
