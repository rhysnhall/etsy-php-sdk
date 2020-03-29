# Etsy PHP SDK
An unofficial PHP SDK for the Etsy API.

This package is still in development. It is not stable to directly use.

## Requirements
PHP 5.6.0 or greater.

## Usage

### Authorizing your app
The Etsy API uses OAuth 1.0 authentication. You can read more about authenticating with Etsy on their [documentation](https://www.etsy.com/developers/documentation/getting_started/oauth).

The first step in OAuth is to obtain temporary credentials. You will need an existing API consumer key and secret which you can obtain by registering an app [here](https://www.etsy.com/developers/register).
```php
Etsy::setConfig([
  'consumer_key' => $consumer_key,
  'consumer_secret' => $consumer_secret
]);
$etsy = new Etsy;
```

Obtain the temporary credentials and save them.
```php
$temp_credentials = $etsy->getTemporaryCredentials();
```

Generate a URL to redirect the user to authorize access to your app.
```php
$redirect_url = $etsy->getAuthorizationUrl();
```

Depending on your apps requirements, you will need to specify the [permission scopes](https://www.etsy.com/developers/documentation/getting_started/oauth#section_permission_scopes) you want to authorize access for. This should be done when setting the config. You can use either an array or a string to define the scope.

Additionally, you will most likely want to set a callback URL.
```php
Etsy::setConfig([
  'consumer_key' => $consumer_key,
  'consumer_secret' => $consumer_secret,
  'scope' => ['listings_r', 'transactions_r'],
  'callback_uri' => 'my-etsy-app/authorize'
]);
```

Upon successful authorization, the user will be redirected to your URL along with two query string parameters called **oauth_verifier** and **oauth_token**.

You now only have a small window of time to complete the last step before the verifier expires.

To complete authorization, you will need to request OAuth token credentials.
```php
$token_credentials = $etsy->getTokenCredentials($temp_creds, $oauth_token, $oauth_verifier);
```

Save these credentials as they will remain valid until the they revoked, and can be reused for the specific user moving forward.

The next time you set the Etsy config, include these access credentials to skip the need to repeat the above process.
```php
Etsy::setConfig([
  'consumer_key' => $consumer_key,
  'consumer_secret' => $consumer_secret,
  'access_key' => $access_key,
  'access_secret' => $access_secret
]);
```

### General usage
