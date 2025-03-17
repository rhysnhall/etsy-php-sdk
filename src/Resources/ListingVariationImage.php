<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Variation Image class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-VariationImage
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingVariationImage extends Resource
{
    /**
     * Get variation images for a listing.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @return \Etsy\Collection[\Etsy\Resources\ListingVariationImage]
     */
    public static function all(
        int $shop_id,
        int $listing_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/listings/{$listing_id}/variation-images",
            "ListingVariationImage"
        );
    }

    /**
     * Update variation images for a listing.
     *
     * @param int $shop_id
     * @param $listing_id
     * @param array $variation_images
     * @return \Etsy\Resources\ListingVariationImage
     */
    public static function update(
        int $shop_id,
        int $listing_id,
        array $variation_images
    ): ?\Etsy\Resources\ListingVariationImage {
        return self::request(
            "POST",
            "/application/shops/{$shop_id}/listings/{$listing_id}/variation-images",
            "ListingVariationImage",
            [
            'variation_images' => $variation_images
      ]
        );
    }
}
