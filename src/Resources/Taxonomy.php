<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Taxonomy resource class. Represents a taxonomy within Etsy. These are essentially categories used for listing.
 *
 * @link https://www.etsy.com/developers/documentation/reference/taxonomy
 * @author Rhys Hall hello@rhyshall.com
 */
class Taxonomy extends Resource {

  /**
   * Get the propertys for this Taxonomy node.
   *
   * @return \Etsy\Collection
   */
  public function getProperties() {
    return $this->request(
        'GET',
        "/taxonomy/seller/{$this->id}/properties",
        "TaxonomyProperty"
      );
  }

}
