<?php

namespace Etsy\Resources;

use Etsy\Resource;
use Etsy\Utils\Date;
use Etsy\Exception\{
    SdkException,
    ApiException
};
use Etsy\Resources\{
    Listing,
    ProductionPartner,
    ShopSection,
    ReturnPolicy,
    ShippingProfile,
    Review,
    Receipt,
    LedgerEntry,
    Payment,
    Transaction
};

/**
 * Shop resource class. Represents a Etsy user's shop.
 *
 * @link https://developers.etsy.com/documentation/reference/#tag/Shop
 * @author Rhys Hall hello@rhyshall.com
 */
class Shop extends Resource
{
    /**
     * @var array
     */
    protected $_saveable = [
      'title',
      'announcement',
      'sale_message',
      'digital_sale_message',
      'policy_additional'
    ];

    /**
     * Gets an Etsy shop.
     *
     * @param int $shop_id
     * @return ?Etsy\Resources\Shop
     */
    public static function get(
        int $shop_id
    ): ?\Etsy\Resources\Shop {
        return self::request(
            "GET",
            "/application/shops/{$shop_id}",
            "Shop"
        );
    }

    /**
     * Updates a shop.
     *
     * @param int $shop_id
     * @param array $data
     * @return ?Etsy\Resources\Shop
     */
    public static function update(
        int $shop_id,
        array $data
    ): ?\Etsy\Resources\Shop {
        return self::request(
            'PUT',
            "/application/shops/{$shop_id}",
            "Shop",
            $data
        );
    }

    /**
     * Search for shops.
     *
     * @param string $keyword
     * @param array $params
     * @return Etsy\Collection[Etsy\Resources\Shop]
     */
    public static function all(
        string $keyword,
        array $params = []
    ): \Etsy\Collection {
        if (!strlen(trim($keyword))) {
            throw new ApiException("You must specify a keyword when searching for Etsy shops.");
        }
        $params['shop_name'] = $keyword;
        return self::request(
            'GET',
            "/application/shops",
            "Shop",
            $params
        );
    }

    /**
     * Get a count of all shops.
     *
     * @param string $keyword
     * @param array $params
     * @return int
     */
    public static function count(
        string $keyword,
        array $params = []
    ): int {
        $result = self::all($keyword, $params);
        if (!$result || !isset($result->count)) {
            return 0;
        }
        return $result->count;
    }

    /**
     * Saves updates to the current shop.
     *
     * @param array $data
     * @return Etsy\Resources\Shop
     */
    public function save(
        ?array $data = null
    ): \Etsy\Resources\Shop {
        if (!$data) {
            $data = $this->getSaveData();
        }
        if (count($data) == 0) {
            return $this;
        }
        return $this->updateRequest(
            "/application/shops/{$this->shop_id}",
            $data
        );
    }

    /**
     * Get all listings for the shop.
     *
     * @param array $params
     * @return Etsy\Collection[Etsy\Resources\Listing]
     */
    public function listings(
        array $params = []
    ): \Etsy\Collection {
        return Listing::allByShop($this->shop_id, $params);
    }

    /**
     * Get all active listings for the shop.
     *
     * @param array $params
     * @return Etsy\Collection[Etsy\Resources\Listing]
     */
    public function activeListings(
        array $params = []
    ): \Etsy\Collection {
        return Listing::allActiveByShop($this->shop_id, $params);
    }

    /**
     * Get all featured listings for the shop.
     *
     * @param array $params
     * @return Etsy\Collection[Etsy\Resources\Listing]
     */
    public function featuedListings(
        array $params = []
    ): \Etsy\Collection {
        return Listing::allFeaturedByShop($this->shop_id, $params);
    }

    /**
     * Get all production partners for the shop.
     *
     * @return Etsy\Collection[Etsy\Resources\ProductionPartner]
     */
    public function productionPartners(): \Etsy\Collection
    {
        return ProductionPartner::all($this->shop_id);
    }

    /**
     * Get all sections for the shop.
     *
     * @return Etsy\Collection[Etsy\Resources\ShopSection]
     */
    public function sections(): \Etsy\Collection
    {
        return ShopSection::all($this->shop_id);
    }

    /**
     * Get a specific section within the shop.
     *
     * @param int $section_id
     * @return Etsy\Resources\ShopSection
     */
    public function section(
        int $section_id
    ): ?ShopSection {
        return ShopSection::get($this->shop_id, $section_id);
    }

    /**
     * Get all return policies for the shop.
     *
     * @return Etsy\Collection[Etsy\Resources\ReturnPolicy]
     */
    public function returnPolicies(): \Etsy\Collection
    {
        return ReturnPolicy::all($this->shop_id);
    }

    /**
     * Get a specific return policy within the shop.
     *
     * @param int $policy_id
     * @return Etsy\Resources\ReturnPolicy
     */
    public function returnPolicy(
        int $policy_id
    ): ?ReturnPolicy {
        return ReturnPolicy::get($this->shop_id, $policy_id);
    }

    /**
     * Get all shipping profiles for the shop.
     *
     * @return Etsy\Collection[Etsy\Resources\ShippingProfile]
     */
    public function shippingProfiles(): \Etsy\Collection
    {
        return ShippingProfile::all($this->shop_id);
    }

    /**
     * Get a specific shipping profile.
     *
     * @param int $profile_id
     * @return Etsy\Resources\ShippingProfile
     */
    public function shippingProfile(
        int $profile_id
    ): ?ShippingProfile {
        return ShippingProfile::get($this->shop_id, $profile_id);
    }

    /**
     * Get all reviews for the shop.
     *
     * @param array $params
     * @return \Etsy\Collection[Etsy\Resources\Review]
     */
    public function reviews(
        array $params = []
    ): \Etsy\Collection {
        return Review::all($this->shop_id, $params);
    }

    /**
     * Get all receipts for the shop.
     *
     * @param array @params
     * @return Etsy\Collection[Etsy\Resources\Receipt]
     */
    public function receipts(
        array $params = []
    ): \Etsy\Collection {
        return Receipt::all($this->shop_id, $params);
    }

    /**
     * Get a single receipt.
     *
     * @param int $receipt_id
     * @return \Etsy\Resources\Receipt
     */
    public function receipt(
        int $receipt_id
    ): ?Receipt {
        return Receipt::get($this->shop_id, $receipt_id);
    }

    /**
     * Get all ledger entries for a shop.
     *
     * @param array $params
     * @return \Etsy\Collection[\Etsy\Resources\LedgerEntry]
     */
    public function ledgerEntries(
        array $params = []
    ): \Etsy\Collection {
        return LedgerEntry::all($this->shop_id, $params);
    }

    /**
     * Get a single ledger entry.
     *
     * @param int $ledger_entry_id
     * @return Etsy\Resources\LedgerEntry
     */
    public function ledgerEntry(
        int $ledger_entry_id
    ): ?LedgerEntry {
        return LedgerEntry::get($this->shop_id, $ledger_entry_id);
    }

    /**
     * Get payments for the shop.
     *
     * @param int|array $payment_ids
     * @return \Etsy\Collection[\Etsy\Resources\Payment]
     */
    public function payments(
        int|array $payment_ids
    ): \Etsy\Collection {
        return Payment::all($this->shop_id, $payment_ids);
    }

    /**
     * Get transactions for the shop.
     *
     * @param array $params
     * @return \Etsy\Collection[\Etsy\Resources\Transaction]
     */
    public function transactions(
        array $params = []
    ): \Etsy\Collection {
        return Transaction::all($this->shop_id, $params);
    }

}
