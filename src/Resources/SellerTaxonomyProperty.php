<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * TaxonomyProperty resource class. Represents an optional listing field for a specific taxonomy.
 *
 * @link https://developers.etsy.com/documentation/reference/#operation/getPropertiesByTaxonomyId
 * @author Rhys Hall hello@rhyshall.com
 */
class SellerTaxonomyProperty extends Resource
{
    /**
     * Get all properties for a specific seller taxonomy node.
     *
     * @param int $taxonomy_id
     * @return \Etsy\Collection[Etsy\Resources\SellerTaxonomyProperty]
     */
    public static function all(
        int $taxonomy_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/seller-taxonomy/nodes/{$taxonomy_id}/properties",
            "SellerTaxonomyProperty"
        );
    }
}
