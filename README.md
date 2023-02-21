# Etsy PHP SDK
A PHP SDK for the Etsy API v3.

This package is still in development.

## Requirements
PHP 7.1 or greater.

## Install
Install the package using composer.
```php
composer require erankco/etsy-php-sdk
```

Include the OAuth client and Etsy class.
```php
use Etsy\Etsy;
use Etsy\OAuth\Client;
```

## Usage

### Authorizing your app
The Etsy API uses OAuth 2.0 authentication. You can read more about authenticating with Etsy on their [documentation](https://developers.etsy.com/documentation/essentials/authentication).

The first step in OAuth2 is to request an OAuth token. You will need an existing App API key which you can obtained by registering an app [here](https://www.etsy.com/developers/register).
```php
$client = new Etsy\OAuth\Client($api_key);
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

You'll be provided with both an access token and a refresh token. The access token has a valid duration of 3600 seconds (1 hour). Save both of these for late ruse.

#### Refreshing your token

You can refresh your authorization token using the refresh token that was previously provided. This will provide you with a new valid access token and another refresh token.

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

Create a new instance of the Etsy class using your App API key and a user's access token.

```php
$etsy = new Etsy\Etsy($api_key, $access_token);

// Get user.
$user = $etsy->getUser();

// Get shop.
$shop = $user->getShop();

// Update shop.
$shop->update([
  'title' => 'My exciting shop!'
]);
```

###### Collections
When there is more than one result a collection will be returned.
```php
$reviews = $shop->getReviews();

// Get first review.
$first = $reviews->first();

// Get 100 results using pagination.
foreach($reviews->paginate(100) as $review) {
  ...
}
```

---

Full documentation will be available soon. Email [hello@rhyshall.com](mailto:hello@rhyshall.com) for any assistance.

## Contributing
Help improve this SDK by contributing.

Before opening a pull request, please first discuss the proposed changes via Github issue or <a href="mailto:hello@rhyshall.com">email</a>.

## License
This project is licensed under the MIT License - see the [LICENSE](https://github.com/rhysnhall/etsy-php-sdk/blob/master/LICENSE.md) file for details
