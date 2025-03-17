<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * ProductionPartner resource class. Represents a Shop production partner in Etsy.
 *
 * @link https://developers.etsy.com/documentation/reference#tag/Shop-ProductionPartner
 * @author Rhys Hall hello@rhyshall.com
 */
class ProductionPartner extends Resource
{
    /**
     * Get all production partners for a shop.
     *
     * @param int $shop_id
     * @return Etsy\Collection[Etsy\Resources\ProductionPartner]
     */
    public static function all(
        int $shop_id
    ): \Etsy\Collection {
        return self::request(
            'GET',
            "/application/shops/{$shop_id}/production-partners",
            "ProductionPartner"
        );
    }

}
