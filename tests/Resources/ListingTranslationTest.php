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

  private function listingWithTranslations(): \Etsy\Resources\Listing {
    $this->respond([
      'listing_id' => 11,
      'shop_id' => 5,
      'translations' => [
        'de' => ['listing_id' => 11, 'language' => 'de', 'title' => 'Becher', 'description' => 'Ein Becher', 'tags' => []],
        'fr' => null,
      ],
    ]);
    return \Etsy\Resources\Listing::get(11, ['includes' => 'Translations']);
  }

  public function testIncludedTranslationsBecomeResources(): void {
    $listing = $this->listingWithTranslations();

    $this->assertInstanceOf(ListingTranslation::class, $listing->translations->de);
    $this->assertSame('Becher', $listing->translations->de->title);
    $this->assertNull($listing->translations->fr);
    $this->assertSame('Becher', $listing->toArray()['translations']['de']['title']);
  }

  public function testIncludedTranslationsCanBeSaved(): void {
    $listing = $this->listingWithTranslations();
    $translation = $listing->translation('de');
    $this->assertSame(1, $this->client->requestCount(), 'translation() used the included data.');

    $translation->title = 'Tasse';
    $this->respond(['listing_id' => 11, 'language' => 'de', 'title' => 'Tasse']);
    $translation->save();

    $this->assertLastRequest('PUT', '/application/shops/5/listings/11/translations/de');
  }

  public function testTranslationFetchesLanguagesThatWereNotIncluded(): void {
    $listing = $this->listingWithTranslations();
    $this->respond(['listing_id' => 11, 'language' => 'fr', 'title' => 'Tasse']);

    $this->assertSame('Tasse', $listing->translation('fr')->title);
    $this->assertLastRequest('GET', '/application/shops/5/listings/11/translations/fr');
  }
}
