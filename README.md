# Etsy PHP SDK
A PHP SDK for the Etsy API v3.

Proper documentation still to come. Want to write it for me? I'll buy you an iced latte.

## Requirements
PHP 8 or greater.

## Install
Install the package using composer.
```php
composer require rhysnhall/etsy-php-sdk
```

Include the Etsy class.
```php
use Etsy\Etsy;

$etsy = new Etsy(
  $client_id,
  $access_token
);

// Do the Etsy things.
```

## Usage

### Authorizing your app
The Etsy API uses OAuth 2.0 authentication. You can read more about authenticating with Etsy on their [documentation](https://developers.etsy.com/documentation/essentials/authentication).

The first step in OAuth2 is to request an OAuth token. You will need an existing App API key which you can obtained by registering an app [here](https://www.etsy.com/developers/register).
```php
$client = new Etsy\OAuth\Client($client_id);
```

Generate a URL to redirect the user to authorize access to your app.
```php
$url = $client->getAuthorizationUrl(
  $redirect_uri,
  $scopes,
  $code_challenge,
  $nonce
);
```

###### Redirect URI
You must set an authorized callback URL. Check out the [Etsy documentation](https://developers.etsy.com/documentation/essentials/authentication#redirect-uris) for further information.

###### Scope
Depending on your apps requirements, you will need to specify the [permission scopes](https://developers.etsy.com/documentation/essentials/authentication#scopes) you want to authorize access for.
```php
$scopes = ["listings_d", "listings_r", "listings_w", "profile_r"];
```

You can get all scopes, but it is generally recommended to only get what you need.
```php
$scopes = \Etsy\Utils\PermissionScopes::ALL_SCOPES;
```

###### Code challenge
You'll need to generate a [PKCE code challenge](https://developers.etsy.com/documentation/essentials/authentication#proof-key-for-code-exchange-pkce) and save this along with the verifier used to generate the challenge. You are welcome to generate your own, or let the SDK do this for you.
```php
[$verifier, $code_challenge] = $client->generateChallengeCode();
```

###### Nonce
The nonce is a single use token used for CSRF protection. You can use any token you like but it is recommended to let the SDK generate one for you each time you authorize a user. Save this for verifying the response later on.
```php
$nonce = $client->createNonce();
```


The URL will redirect your user to the Etsy authorization page. If the user grants access, Etsy will send back a request with an authorization code and the nonce (state).
```curl
https://www.example.com/some/location?
      code=bftcubu-wownsvftz5kowdmxnqtsuoikwqkha7_4na3igu1uy-ztu1bsken68xnw4spzum8larqbry6zsxnea4or9etuicpra5zi
      &state=superstate
```

It is up to you to validate the nonce. If they do not match you should discard the response.

For more information on Etsy's response, check out the [documentation here](https://developers.etsy.com/documentation/essentials/authentication#step-2-grant-access).

The final step is to get the access token for the user. To do this you will need to make a request using the code that was just returned by Etsy. You will also need to pass in the same callback URL as the first request and the verifier used to generate the PKCE code challenge.
```php
[$access_token, $refresh_token] = $client->requestAccessToken(
  $redirect_uri,
  $code,
  $verifier
);
```

You'll be provided with both an access token and a refresh token. The access token has a valid duration of 3600 seconds (1 hour). Save both of these for late use.

#### Refreshing your token

You can refresh your authorization token (even after it has expired) using the refresh token that was previously provided. This will provide you with a new valid access token and another refresh token.

```php
[$access_token, $refresh_token] = $client->refreshAccessToken($refresh_token);
```

The [Etsy documentation](https://developers.etsy.com/documentation/essentials/authentication#requesting-a-refresh-oauth-token) states that refreshed access tokens have a duration of 86400 seconds (24 hours) but on testing they appear to only remain valid for up 3600 seconds (1 hour).

#### Exchanging legacy OAuth 1.0 token for OAuth 2.0 token
If you previously used v2 of the Etsy API and still have valid authorization tokens for your users, you may swap these over for valid OAuth2 tokens.
```php
[$access_token, $refresh_token] = $client->exchangeLegacyToken($legacy_token);
```

This will provide you with a brand new set of OAuth2 access and refresh tokens.

### Basic use

Create a new instance of the Etsy class using your App API key and a user's access token. **You must always initialize the Etsy resource before calling any resources**.

```php
use Etsy\Etsy;
use Etsy\Resources\User;

$etsy = new Etsy($apiKey, $accessToken);

// Get the authenticated user.
$user = User::me();

// Get the users shop.
$shop = $user->shop();
```

#### Resources
Most calls will return a `Resource`. Resources contain a number of methods that streamline your interaction with the Etsy API.
```php
// Get a Listing Resource
$listing = \Etsy\Resources\Listing::get($shopId);
```

Resources contain the API response from Etsy as properties.
```php
$listingTitle = $listing->title;
```

##### Associations
Resources will return associations as their respective Resource when appropriate. For example the bellow call will return the `shop` property as an instance of `Etsy\Resources\Shop`.
```php
$shop = $listing->shop;
```

##### `toJson`
The `toJson` method will return the Resource as a JSON encoded object.
```php
$json = $listing->toJson();
```

##### `toArray`
The `toArray` method will return the Resource as an array.
```php
$array = $listing->toArray();
```

#### Collections
When there is more than one result a collection will be returned.
```php
$reviews = Review::all();
```

Results are stored as an array of `Resource` the `data` property of the collection.
```php
$firstReview = $reviews->data[0];
```

Collections contain a handful of useful methods.

##### `first`
Get the first item in the collection.
```php
$firstReview = $reviews->first();
```

##### `count`
Get the number of results in the collection. Not be confused with the `count` property which displays the number of results in a full Etsy resource.
```php
$count = $reviews->count();
```

##### `append`
Append a property to each item in the collection.
```php
$reviews->append(['shop_id' => $shopId]);
```

##### `paginate`
Most Etsy methods are capped at 100 results per call. You can use the `paginate` method to get more results than this (up to 500 results).
```php
// Get 100 results using pagination.
foreach($reviews->paginate(200) as $review) {
  ...
}
```

##### `toJson`
Returns the items in the collection as an array of JSON strings.
```php
$jsonArray = $reviews->toJson();
```

#### Direct Requests
You can make direct requests to the Etsy API using the static `$client` property of the Etsy class.

```php
$response = Etsy::$client->get(
  "/application/listings/active",
  [
    "limit" => 25
  ]
);
```

If you still want to use the Resources classes you can convert the response into a Resource.

```php
$listings = Etsy::getResource(
  $response,
  'Listing'
);
```

---

Full documentation will be available soon. Email [hello@rhyshall.com](mailto:hello@rhyshall.com) for any assistance.

## Contributing
Help improve this SDK by contributing.

Before opening a pull request, please first discuss the proposed changes via Github issue or <a href="mailto:hello@rhyshall.com">email</a>.

## License
This project is licensed under the MIT License - see the [LICENSE](https://github.com/rhysnhall/etsy-php-sdk/blob/master/LICENSE.md) file for details
