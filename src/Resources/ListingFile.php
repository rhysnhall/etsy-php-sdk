<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing File class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-File
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingFile extends Resource
{
    /**
     * Delete a listing file.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param int $file_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $listing_id,
        int $file_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/listings/{$listing_id}/files/{$file_id}"
        );
    }

    /**
     * Get a listing file.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param int $file_id
     * @return \Etsy\Resources\ListingFile
     */
    public static function get(
        int $shop_id,
        int $listing_id,
        int $file_id
    ): ?\Etsy\Resources\ListingFile {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/listings/{$listing_id}/files/{$file_id}",
            "ListingFile"
        );
    }

    /**
     * Get all files for a listing.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @return \Etsy\Collection[\Etsy\Resources\ListingFile]
     */
    public static function all(
        int $shop_id,
        int $listing_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/listings/{$listing_id}/files",
            "ListingFile"
        );
    }

    /**
     * Upload a listing file.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param array $data
     * @return \Etsy\Resources\ListingFile
     */
    public static function create(
        int $shop_id,
        int $listing_id,
        array $data
    ): ?\Etsy\Resources\ListingFile {
        return self::request(
            "POST",
            "/application/shops/{$shop_id}/listings/{$listing_id}/files",
            "ListingFile",
            $data
        );
    }
}
