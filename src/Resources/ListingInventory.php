<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Inventory class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Inventory
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingInventory extends Resource
{
    /**
     * @var array
     */
    protected $_associations = [
      "listing" => "Listing",
      "products" => "ListingProduct"
    ];

    /**
     * Get the inventory for a listing.
     *
     * @param int $listing_id
     * @param array @params
     * @return \Etsy\Resources\ListingInventory
     */
    public static function get(
        int $listing_id,
        array $params = []
    ): ?\Etsy\Resources\ListingInventory {
        $inventory = self::request(
            "GET",
            "/application/listings/{$listing_id}/inventory",
            "ListingInventory",
            $params
        );
        if ($inventory) {
            $inventory->assignListingId($listing_id);
        }
        return $inventory;
    }

    /**
     * Update listing inventory.
     *
     * @param $listing_id
     * @param array $data
     * @return \Etsy\Resources\ListingInventory
     */
    public static function update(
        int $listing_id,
        array $data
    ): ?\Etsy\Resources\ListingInventory {
        $inventory = self::request(
            "PUT",
            "/application/listings/{$listing_id}/inventory",
            "ListingInventory",
            $data
        );
        if ($inventory) {
            $inventory->assignListingId($listing_id);
        }
        return $inventory;
    }

    /**
     * Asssigns the listing ID to the inventory resource.
     *
     * @param int $listing_id
     * @return void
     */
    private function assignListingId(
        int $listing_id
    ) {
        $this->listing_id = $listing_id;
        array_map(
            (function ($product) use ($listing_id) {
                $product->listing_id = $listing_id;
            }),
            ($inventory->products ?? [])
        );
    }

}
