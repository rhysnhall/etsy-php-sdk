<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Review resource class. Represents a Shop review in Etsy.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/Review
 * @author Rhys Hall hello@rhyshall.com
 */
class Review extends Resource
{
    /**
     * Get all Shop reviews.
     *
     * @param int $shop_id
     * @param array $params
     * @return \Etsy\Collection[\Etsy\Resources\Review]
     */
    public static function all(
        int $shop_id,
        array $params = []
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/reviews",
            "Review",
            $params
        );
    }

    /**
     * Get all listing reviews.
     *
     * @param int $listing_id
     * @param array $params
     * @return \Etsy\Collection[\Etsy\Resources\Review]
     */
    public static function allByListing(
        int $listing_id,
        array $params = []
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/listings/{$listing_id}/reviews",
            "Review",
            $params
        );
    }
}
