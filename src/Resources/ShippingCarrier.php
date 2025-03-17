<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Shipping Carrier class.
 *
 * @link https://developers.etsy.com/documentation/reference/#operation/getShippingCarriers
 * @author Rhys Hall hello@rhyshall.com
 */
class ShippingCarrier extends Resource
{
    /**
     * Retrieves a list of available shipping carriers and the mail classes associated with them for a given country
     *
     * @param string $iso_code
     * @return \Etsy\Collection[Etsy\Resources\ShippingCarrier]
     */
    public static function all(
        string $iso_code
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shipping-carriers",
            "ShippingCarrier",
            [
            "origin_country_iso" => $iso_code
      ]
        );
    }
}
