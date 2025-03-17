<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Buyer taxonomy resource.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/BuyerTaxonomy
 * @author Rhys Hall hello@rhyshall.com
 */
class BuyerTaxonomy extends Resource
{
    /**
     * Get the full hierarchy tree of buyer taxonomy nodes.
     *
     * @return \Etsy\Collection[Etsy\Resources\BuyerTaxonomy]
     */
    public static function all(): \Etsy\Collection
    {
        return self::request(
            "GET",
            "/application/buyer-taxonomy/nodes",
            "BuyerTaxonomy"
        );
    }

}
