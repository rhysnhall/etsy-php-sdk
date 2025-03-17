<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Property class.
 *
 * @link https://developers.etsy.com/documentation/reference#operation/getListingProperties
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingProperty extends Resource
{
    /**
     * @var array
     */
    protected $_saveable = [
      'value_ids',
      'values',
      'scale_id'
    ];

    /**
     * Get all properties for a listing.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @return Etsy\Collection[Etsy\Resources\ListingProperty]
     */
    public static function all(
        int $shop_id,
        int $listing_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/listings/{$listing_id}/properties",
            "ListingProperty"
        )->append([
          'shop_id' => $shop_id,
          'listing_id' => $listing_id
        ]);
    }

    /**
     * Updates a listing property.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param int $property_id
     * @param array $data
     * @return Etsy\Resources\ListingProperty
     */
    public static function update(
        int $shop_id,
        int $listing_id,
        int $property_id,
        $data
    ): ?\Etsy\Resources\ListingProperty {
        $property = self::request(
            "PUT",
            "/application/shops/{$shop_id}/listings/{$listing_id}/properties/{$property_id}",
            "ListingProperty",
            $data
        );
        if ($property) {
            $property->shop_id = $shop_id;
            $property->listing_id = $listing_id;
        }
        return $property;
    }

    /**
     * Saves updates to the current listing property.
     *
     * @param array $data
     * @return \Etsy\Resources\ListingProperty
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\ListingProperty {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/properties/{$this->property_id}",
            $data
        );
    }

    /**
     * Delete a listing property.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param int $property_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $listing_id,
        int $property_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/listings/{$listing_id}/properties/{$property_id}"
        );
    }
}
