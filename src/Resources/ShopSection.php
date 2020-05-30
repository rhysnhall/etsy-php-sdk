<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * ShopSection resource class. Represents a Etsy shop section.
 * 
 * @link https://www.etsy.com/developers/documentation/reference/shopsection
 * @author Rhys Hall hello@rhyshall.com
 */
class ShopSection extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Shop' => 'Shop',
    'Listings' => 'Listing',
    'Translations' => 'ShopSectionTranslation'
  ];

  /**
   * Updates the shop section.
   *
   * @param array $data
   * @return ShopSection
   */
  public function update(array $data) {
    return $this->updateRequest(
        "/shops/{$this->shop_id}/sections/{$this->shop_section_id}",
        $data
      );
  }

  /**
   * Deletes the shop section.
   *
   * @return boolean
   */
  public function delete() {
    return $this->deleteRequest(
      "/shops/{$this->shop_id}/sections/{$this->shop_section_id}"
    );
  }

}
