<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Translation class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Translation
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingTranslation extends Resource
{
    /**
     * Get a listing translation.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param string $language
     * @return \Etsy\Resources\ListingTranslation
     */
    public static function get(
        int $shop_id,
        int $listing_id,
        string $language
    ): ?\Etsy\Resources\ListingTranslation {
        $translation = self::request(
            "GET",
            "/application/shops/{$shop_id}/listings/{$listing_id}/translations/{$language}",
            "ListingTranslation"
        );
        if ($translation) {
            $translation->shop_id = $shop_id;
        }
        return $translation;
    }

    /**
     * Create a listing translation.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param string $language
     * @param array $data
     * @return \Etsy\Resources\ListingTranslation
     */
    public static function create(
        int $shop_id,
        int $listing_id,
        string $language,
        array $data
    ): ?\Etsy\Resources\ListingTranslation {
        $translation = self::request(
            "POST",
            "/application/shops/{$shop_id}/listings/{$listing_id}/translations/{$language}",
            "ListingTranslation",
            $data
        );
        if ($translation) {
            $translation->shop_id = $shop_id;
        }
        return $translation;
    }

    /**
     * Update a listing translation.
     *
     * @param int $shop_id
     * @param int $listing_id
     * @param string $language
     * @param array $data
     * @return \Etsy\Resources\ListingTranslation
     */
    public static function update(
        int $shop_id,
        int $listing_id,
        string $language,
        array $data
    ): ?\Etsy\Resources\ListingTranslation {
        $translation = self::request(
            "PUT",
            "/application/shops/{$shop_id}/listings/{$listing_id}/translations/{$language}",
            "ListingTranslation",
            $data
        );
        if ($translation) {
            $translation->shop_id = $shop_id;
        }
        return $translation;
    }

    /**
     * Save updates to the current translation.
     *
     * @param array $data
     * @return \Etsy\Resources\ListingTranslation
     */
    public function save(
        ?array $data = nul
    ): \Etsy\Resources\ListingTranslation {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/listings/{$this->listing_id}/translations/{$this->language}",
            $data
        );
    }

}
