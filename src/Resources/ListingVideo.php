<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Variation Video class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Video
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingVideo extends Resource
{
    /**
     * Get videos for a listing.
     *
     * @param int $listing_id
     * @return \Etsy\Collection[\Etsy\Resources\ListingVideo]
     */
    public static function all(
        int $listing_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/listings/{$listing_id}/videos",
            "ListingVideo"
        );
    }

    /**
     * Get a single listing video.
     *
     * @param int $listing_id
     * @param int $video_id
     * @return \Etsy\Resources\ListingVideo
     */
    public static function get(
        int $listing_id,
        int $video_id
    ): ?\Etsy\Resources\ListingVideo {
        return self::request(
            "GET",
            "/application/listings/{$listing_id}/videos/{$video_id}",
            "ListingVideo"
        );
    }

    /**
     * Create a new listing video.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param array $data
     * @return \Etsy\Resources\ListingVideo
     */
    public static function create(
        int $shop_id,
        int $listing_id,
        array $data
    ): ?\Etsy\Resources\ListingVideo {
        return self::request(
            "POST",
            "/application/shops/{$shop_id}/listings/{$listing_id}/videos",
            "ListingVideo",
            $data
        );
    }

    /**
     * Delete a listing video.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param int $video_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $listing_id,
        int $video_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/listings/{$listing_id}/videos/{$video_id}"
        );
    }

}
