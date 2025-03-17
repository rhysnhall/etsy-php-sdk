<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Resources\Listing;

/**
 * ShopSection resource class. Represents a Etsy shop section.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/Shop-Section
 * @author Rhys Hall hello@rhyshall.com
 */
class ShopSection extends Resource
{
    /**
     * @var array
     */
    protected $_saveable = [
      'title'
    ];

    /**
     * Get all sections for a shop.
     *
     * @param int $shop_id
     * @return Etsy\Collection[Etsy\Resources\ShopSection]
     */
    public static function all(
        int $shop_id
    ): \Etsy\Collection {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}/sections",
            "ShopSection"
        )->append(['shop_id' => $shop_id]);
    }

    /**
     * Get a shop section.
     *
     * @param int $shop_id
     * @param int $section_id
     * @return Etsy\Resources\ShopSection
     */
    public static function get(
        int $shop_id,
        int $section_id
    ): ?\Etsy\Resources\ShopSection {
        $section = self::request(
            "GET",
            "/application/shops/{$shop_id}/sections/{$section_id}",
            "ShopSection"
        );
        if ($section) {
            $section->shop_id = $shop_id;
        }
        return $section;
    }

    /**
     * Create a new shop section.
     *
     * @param int $shop_id
     * @param array $data
     * @return Etsy\Resources\ShopSection
     */
    public static function create(
        int $shop_id,
        array $data
    ): ?\Etsy\Resources\ShopSection {
        $section = self::request(
            "POST",
            "/application/shops/{$shop_id}/sections",
            "ShopSection",
            $data
        );
        if ($section) {
            $section->shop_id = $shop_id;
        }
        return $section;
    }

    /**
     * Update a shop section.
     *
     * @param int $shop_id
     * @param int $section_id
     * @param array $data
     * @return Etsy\Resources\ShopSection
     */
    public static function update(
        int $shop_id,
        int $section_id,
        array $data
    ): ?\Etsy\Resources\ShopSection {
        $section = self::request(
            "PUT",
            "/application/shops/{$shop_id}/sections/{$section_id}",
            "ShopSection",
            $data
        );
        if ($section) {
            $section->shop_id = $shop_id;
        }
        return $section;
    }

    /**
     * Deletes a shop section.
     *
     * @param int $shop_id
     * @param int $section_id
     * @return bool
     */
    public static function delete(
        int $shop_id,
        int $section_id
    ): bool {
        return self::deleteRequest(
            "/application/shops/{$shop_id}/sections/{$section_id}",
        );
    }

    /**
     * Saves updates to the current section.
     *
     * @param array $data
     * @return Etsy\Resources\ShopSection
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\ShopSection {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/sections/{$this->shop_section_id}",
            $data
        );
    }

    /**
     * Get all listings within this shop section.
     *
     * @param array $params
     * @return \Etsy\Collection[\Etsy\Resources\Listing]
     */
    public function listings(
        array $params = []
    ): \Etsy\Collection {
        return Listing::allByShopSections(
            $this->shop_id,
            $this->shop_section_id,
            $params
        );
    }

}
