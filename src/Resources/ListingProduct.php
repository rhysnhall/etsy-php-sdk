<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Listing Product class.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/ShopListing-Product
 * @author Rhys Hall hello@rhyshall.com
 */
class ListingProduct extends Resource
{
    /**
     * @var array
     */
    protected $_associations = [
      "offerings" => "ListingOffering"
    ];

    /**
     * Get a listing product.
     *
     * @param int $listing_id
     * @param int $product_id
     * @return \Etsy\Resources\ListingProduct
     */
    public static function get(
        int $listing_id,
        int $product_id
    ): ?\Etsy\Resources\ListingProduct {
        $product = self::request(
            "GET",
            "/application/listings/{$listing_id}/inventory/products/{$product_id}",
            "ListingProduct"
        );
        if ($product) {
            $product->listing_id = $listing_id;
        }
        return $product;
    }

    /**
     * Get a specific listing offering.
     *
     * @param int $offering_id
     * @return \Etsy\Resources\ListingOffering
     */
    public function offering(
        int $offering_id
    ): ?\Etsy\Resources\ListingOffering {
        return ListingOffering::get(
            $this->listing_id,
            $this->product_id,
            $offering_id
        );
    }

}
