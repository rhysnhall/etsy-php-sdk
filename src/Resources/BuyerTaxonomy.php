<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * BuyerTaxonomy resource class. Represents a buyer taxonomy within Etsy. These are essentially categories used for listing.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/BuyerTaxonomy
 * @author Andrew Christensen andrew@critical-code.com
 */
class BuyerTaxonomy extends Resource {

  /**
   * Get the properties for this Buyer Taxonomy node.
   *
   * @return \Etsy\Collection
   */
  public function getProperties() {
    return $this->request(
        'GET',
        "/application/buyer-taxonomy/nodes/{$this->id}/properties",
        "BuyerTaxonomyProperty"
      );
  }

}
