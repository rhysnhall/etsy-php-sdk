<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Image class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Image
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingImage extends Resource
{
    /**
     * Get a listing image.
     *
     * @param int $listing_id
     * @param int $image_id
     * @return \Etsy\Resources\ListingImage
     */
    public static function get(
        int $listing_id,
        int $image_id
    ): ?\Etsy\Resources\ListingImage {
        return self::request(
            "GET",
            "/application/listings/{$listing_id}/images/{$image_id}",
            "ListingImage"
        );
    }

    /**
     * Get images for a listing.
     *
     * @param int $listing_id
     * @return \Etsy\Collection[\Etsy\Resources\ListingImage]
     */
    public static function all(
        int $listing_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/listings/{$listing_id}/images",
            "ListingImage"
        );
    }

    /**
     * Create a new image.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param array $data
     * @return \Etsy\Resources\ListingImage
     */
    public static function create(
        int $shop_id,
        int $listing_id,
        array $data
    ): ?\Etsy\Resources\ListingImage {
        return self::request(
            "POST",
            "/application/shops/{$shop_id}/listings/{$listing_id}/images",
            "ListingImage",
            $data
        );
    }

    /**
     * Delete a listing image.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param int $image_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $listing_id,
        int $image_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/listings/{$listing_id}/images/{$image_id}"
        );
    }
}
