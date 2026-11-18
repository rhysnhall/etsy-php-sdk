<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\{ListingPersonalization, ListingVariationImage, Shipment};
use Etsy\Tests\TestCase;

/**
 * Endpoints that Etsy only accepts as application/json.
 */
class JsonEndpointsTest extends TestCase {

  private array $questions = [[
    'question_text' => 'Name to engrave',
    'question_type' => 'text_input',
    'required' => false,
  ]];

  public function testPersonalizationUpdateSendsJson(): void {
    $this->respond(['personalization_questions' => $this->questions]);
    ListingPersonalization::update(5, 11, ['personalization_questions' => $this->questions]);

    $this->assertLastRequest('POST', '/application/shops/5/listings/11/personalization');
    $this->assertSame('supports_multiple_personalization_questions=true', $this->lastQuery());
    $this->assertSame(['personalization_questions' => $this->questions], $this->lastJson());
  }

  public function testPersonalizationUpdateSendsFalseExplicitly(): void {
    $this->respond(['personalization_questions' => []]);
    ListingPersonalization::update(5, 11, ['personalization_questions' => []], false);

    $this->assertSame('supports_multiple_personalization_questions=false', $this->lastQuery());
  }

  public function testPersonalizationSavePostsJson(): void {
    $this->respond(['personalization_questions' => []]);
    $personalization = ListingPersonalization::get(5, 11);
    $personalization->personalization_questions = $this->questions;
    $this->respond(['personalization_questions' => $this->questions]);

    $personalization->save();

    $this->assertLastRequest('POST', '/application/shops/5/listings/11/personalization');
    $this->assertSame(['personalization_questions' => $this->questions], $this->lastJson());
  }

  public function testVariationImagesUpdateSendsJson(): void {
    $images = [['property_id' => 200, 'value_id' => 3, 'image_id' => 4]];
    $this->respond(['count' => 1, 'results' => $images]);

    ListingVariationImage::update(5, 11, $images);

    $this->assertLastRequest('POST', '/application/shops/5/listings/11/variation-images');
    $this->assertSame(['variation_images' => $images], $this->lastJson());
  }

  public function testShipmentCreateSendsJson(): void {
    $this->respond(['receipt_id' => 1]);
    Shipment::create(5, 1, ['tracking_code' => 'TRACK1', 'carrier_name' => 'usps']);

    $this->assertLastRequest('POST', '/application/shops/5/receipts/1/tracking');
    $this->assertSame(['tracking_code' => 'TRACK1', 'carrier_name' => 'usps'], $this->lastJson());
  }
}
