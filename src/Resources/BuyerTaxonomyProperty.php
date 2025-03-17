<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Buyer taxonomy resource.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/BuyerTaxonomy
 * @author Rhys Hall hello@rhyshall.com
 */
class BuyerTaxonomyProperty extends Resource
{
    /**
     * Get all properties for a specific buyer taxonomy node.
     *
     * @param int $taxonomy_id
     * @return \Etsy\Collection[Etsy\Resources\BuyerTaxonomyProperty]
     */
    public static function all(
        int $taxonomy_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/buyer-taxonomy/nodes/{$taxonomy_id}/properties",
            "BuyerTaxonomyProperty"
        );
    }

}
