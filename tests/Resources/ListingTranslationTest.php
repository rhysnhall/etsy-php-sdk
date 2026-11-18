<?php

namespace Etsy\Tests\Resources;

use Etsy\Resources\ListingTranslation;
use Etsy\Tests\TestCase;

class ListingTranslationTest extends TestCase {

  public function testSaveWithoutArgumentsSendsSaveableFields(): void {
    $this->respond(['listing_id' => 11, 'language' => 'de', 'title' => 'Becher', 'description' => 'Ein Becher', 'tags' => []]);
    $translation = ListingTranslation::get(5, 11, 'de');
    $translation->title = 'Tasse';
    $this->respond(['listing_id' => 11, 'language' => 'de', 'title' => 'Tasse']);

    $translation->save();

    $this->assertLastRequest('PUT', '/application/shops/5/listings/11/translations/de');
    $form = $this->lastForm();
    $this->assertSame('Tasse', $form['title']);
    $this->assertSame('Ein Becher', $form['description']);
    $this->assertSame('Tasse', $translation->title);
  }
}
