<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Seller Taxonomy resource class. These are essentially categories used for listings.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/SellerTaxonomy
 * @author Rhys Hall hello@rhyshall.com
 */
class SellerTaxonomy extends Resource
{
    /**
     * Get the full hierarchy tree of buyer taxonomy nodes.
     *
     * @return \Etsy\Collection[Etsy\Resources\SellerTaxonomy]
     */
    public static function all(): \Etsy\Collection
    {
        return self::request(
            "GET",
            "/application/seller-taxonomy/nodes",
            "SellerTaxonomy"
        );
    }

}
