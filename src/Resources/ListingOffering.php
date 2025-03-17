<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Inventory class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Offering
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingOffering extends Resource
{
    /**
     * Get a product offering.
     *
     * @param int $listing_id
     * @param int $product_id
     * @param int $offering_id
     * @return \Etsy\Resources\ListingOffering
     */
    public static function get(
        int $listing_id,
        int $product_id,
        int $offering_id
    ): ?\Etsy\Resources\ListingOffering {
        return self::request(
            "GET",
            "/application/listings/{$listing_id}/products/{$product_id}/offerings/{$offering_id}",
            "ListingOffering"
        );
    }
}
