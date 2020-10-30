<?php

namespace Etsy\Utils;

use Etsy\Exception\SdkException;

/**
 *  Methods for handling and validating addresses.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class Address {

  /**
   * 61 = Australia
   * 79 = Canada
   * 209 = United States
   */
  const VALID_STATES = [
    61 => ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA'],
    79 => ['AB', 'BC', 'MB', 'NB', 'NL', 'NT', 'NS', 'NU', 'ON', 'PE', 'QC', 'SK', 'YT'],
    209 => ['AL', 'AK', 'AS', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FM', 'FL', 'GA', 'GU', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MH', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'MP', 'OH', 'OK', 'OR', 'PW', 'PA', 'PR', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VI', 'VA', 'WA', 'WV', 'WI', 'WY', 'AA', 'AE', 'AP']
  ];

  /**
   * Validates the state value for required countries. Does not return boolean
   * but throws an error if state is invalid.
   *
   * @param integer/string $country_id
   * @param string $state
   * @return void
   */
  public static function validateState($country_id, string $state) {
    if(in_array($country_id, array_keys(self::VALID_STATES))) {
      if(!in_array($state, self::VALID_STATES[$country_id])) {
        throw new SdkException("{$state} is not a valid state for country ID {$country_id}. Valid values are ".implode(', ', self::VALID_STATES[$country_id]));
      }
    }
  }

}
