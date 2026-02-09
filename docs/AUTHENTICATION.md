# Authentication

Complete guide to authenticating with the Etsy API using OAuth 2.0.

## Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [OAuth 2.0 Flow](#oauth-20-flow)
- [Step-by-Step Implementation](#step-by-step-implementation)
- [Token Management](#token-management)
- [Permission Scopes](#permission-scopes)
- [Legacy Token Migration](#legacy-token-migration)
- [Security Best Practices](#security-best-practices)
- [Troubleshooting](#troubleshooting)

## Overview

The Etsy API v3 uses **OAuth 2.0 with PKCE** (Proof Key for Code Exchange) for authentication. This provides secure, token-based access to user data.

### Key Concepts

- **Client ID**: Your app's public identifier
- **Shared Secret**: Your app's private key (required in SDK v1.2.0+)
- **Access Token**: Short-lived token for API requests (1 hour validity)
- **Refresh Token**: Long-lived token to obtain new access tokens (90 days validity)
- **Authorization Code**: One-time code exchanged for tokens
- **PKCE**: Security extension using code challenge/verifier

### Authentication Flow Diagram

```
┌─────────┐                                  ┌─────────┐
│  Your   │                                  │  Etsy   │
│   App   │                                  │   API   │
└────┬────┘                                  └────┬────┘
     │                                            │
     │  1. Generate authorization URL             │
     │────────────────────────────────────>       │
     │                                            │
     │  2. Redirect user to Etsy                  │
     │────────────────────────────────────>       │
     │                                            │
     │       3. User grants permission            │
     │                                            │
     │  4. Etsy redirects back with code          │
     │<────────────────────────────────────       │
     │                                            │
     │  5. Exchange code for tokens               │
     │────────────────────────────────────>       │
     │                                            │
     │  6. Receive access + refresh tokens        │
     │<────────────────────────────────────       │
     │                                            │
     │  7. Make API requests with access token    │
     │────────────────────────────────────>       │
     │                                            │
```

## Prerequisites

### 1. Register Your App

Register your application at: [`https://www.etsy.com/developers/register`](https://www.etsy.com/developers/register)

You'll receive:
- **Client ID** (Keystring)
- **Shared Secret**

### 2. Configure Redirect URI

Set an authorized callback URL in your app settings. This is where Etsy will redirect users after authorization.

**Example**: `https://yourdomain.com/auth/etsy/callback`

**Important**: 
- Must use HTTPS in production
- Must exactly match the URI provided in your authorization request
- Can use localhost for development: `http://localhost:8000/callback`

### 3. Choose Permission Scopes

Determine which [permission scopes](#permission-scopes) your app needs. Only request what you need.

## OAuth 2.0 Flow

### Step 1: Initialize the OAuth Client

```php
use Etsy\OAuth\Client;

$client = new Client(
    $clientId,        // Your app's client ID
    $sharedSecret     // Your app's shared secret
);
```

### Step 2: Generate PKCE Code Challenge

PKCE adds security by ensuring the app exchanging the code is the same one that requested it.

```php
// Let the SDK generate verifier and challenge
[$verifier, $codeChallenge] = $client->generateChallengeCode();

// Store $verifier in session - you'll need it later
$_SESSION['pkce_verifier'] = $verifier;
```

**How it works**:
1. Generate random verifier (64-character string)
2. Create SHA256 hash of verifier
3. Base64URL encode the hash = code challenge
4. Send challenge with authorization request
5. Send verifier when exchanging code for tokens
6. Etsy verifies: `SHA256(verifier) === stored_challenge`

### Step 3: Generate Nonce (State)

The nonce prevents CSRF attacks by validating the response came from your request.

```php
$nonce = $client->createNonce();

// Store in session to verify later
$_SESSION['oauth_nonce'] = $nonce;
```

### Step 4: Generate Authorization URL

```php
$redirectUri = 'https://yourdomain.com/auth/etsy/callback';

$scopes = [
    'listings_r',      // Read listings
    'listings_w',      // Write listings
    'shops_r',         // Read shop info
    'transactions_r'   // Read transactions
];

$authUrl = $client->getAuthorizationUrl(
    $redirectUri,
    $scopes,
    $codeChallenge,
    $nonce
);

// Redirect user to Etsy
header("Location: {$authUrl}");
exit;
```

### Step 5: Handle Callback

When the user authorizes your app, Etsy redirects to your callback URL with parameters:

```
https://yourdomain.com/auth/etsy/callback?code=XXX&state=YYY
```

```php
// In your callback handler
use Etsy\OAuth\Client;

// Verify the nonce/state to prevent CSRF
$returnedNonce = $_GET['state'] ?? '';
$storedNonce = $_SESSION['oauth_nonce'] ?? '';

if ($returnedNonce !== $storedNonce) {
    die('Invalid state parameter. Possible CSRF attack.');
}

// Get the authorization code
$code = $_GET['code'] ?? '';
if (empty($code)) {
    die('No authorization code received.');
}

// Initialize client
$client = new Client($clientId, $sharedSecret);

// Exchange code for tokens
try {
    [$accessToken, $refreshToken] = $client->requestAccessToken(
        $redirectUri,  // Same redirect URI as before
        $code,
        $_SESSION['pkce_verifier']  // PKCE verifier from step 2
    );
    
    // Store tokens securely
    // DO NOT store in cookies or client-side storage
    $_SESSION['etsy_access_token'] = $accessToken;
    $_SESSION['etsy_refresh_token'] = $refreshToken;
    $_SESSION['token_expires_at'] = time() + 3600; // 1 hour from now
    
    // Clean up temporary data
    unset($_SESSION['oauth_nonce']);
    unset($_SESSION['pkce_verifier']);
    
    // Success! Redirect to your app
    header('Location: /dashboard');
    exit;
    
} catch (\Etsy\Exception\OAuthException $e) {
    die('OAuth error: ' . $e->getMessage());
}
```

### Step 6: Make API Requests

```php
use Etsy\Etsy;
use Etsy\Resources\User;

// Initialize Etsy with access token
$etsy = new Etsy(
    $clientId,
    $sharedSecret,
    $_SESSION['etsy_access_token']
);

// Now you can use the API
$user = User::me();
$shop = $user->shop();
```

## Step-by-Step Implementation

### Complete Example

Here's a complete working example with two PHP scripts:

#### authorize.php

```php
<?php
session_start();

require_once 'vendor/autoload.php';

use Etsy\OAuth\Client;

// Your app credentials
$clientId = 'your_client_id_here';
$sharedSecret = 'your_shared_secret_here';
$redirectUri = 'http://localhost:8000/callback.php';

// Initialize OAuth client
$client = new Client($clientId, $sharedSecret);

// Generate PKCE code challenge
[$verifier, $codeChallenge] = $client->generateChallengeCode();
$_SESSION['pkce_verifier'] = $verifier;

// Generate nonce for CSRF protection
$nonce = $client->createNonce();
$_SESSION['oauth_nonce'] = $nonce;

// Define required scopes
$scopes = [
    'listings_r',
    'listings_w',
    'shops_r',
    'transactions_r',
    'profile_r'
];

// Generate authorization URL
$authUrl = $client->getAuthorizationUrl(
    $redirectUri,
    $scopes,
    $codeChallenge,
    $nonce
);

// Redirect to Etsy
header("Location: {$authUrl}");
exit;
```

#### callback.php

```php
<?php
session_start();

require_once 'vendor/autoload.php';

use Etsy\OAuth\Client;
use Etsy\Etsy;
use Etsy\Resources\User;

// Your app credentials
$clientId = 'your_client_id_here';
$sharedSecret = 'your_shared_secret_here';
$redirectUri = 'http://localhost:8000/callback.php';

// Verify state (CSRF protection)
$returnedNonce = $_GET['state'] ?? '';
$storedNonce = $_SESSION['oauth_nonce'] ?? '';

if ($returnedNonce !== $storedNonce) {
    die('Error: Invalid state parameter.');
}

// Get authorization code
$code = $_GET['code'] ?? '';
if (empty($code)) {
    die('Error: No authorization code received.');
}

// Initialize client
$client = new Client($clientId, $sharedSecret);

try {
    // Exchange code for tokens
    [$accessToken, $refreshToken] = $client->requestAccessToken(
        $redirectUri,
        $code,
        $_SESSION['pkce_verifier']
    );
    
    // Store tokens
    $_SESSION['etsy_access_token'] = $accessToken;
    $_SESSION['etsy_refresh_token'] = $refreshToken;
    $_SESSION['token_expires_at'] = time() + 3600;
    
    // Clean up
    unset($_SESSION['oauth_nonce']);
    unset($_SESSION['pkce_verifier']);
    
    // Test the connection
    $etsy = new Etsy($clientId, $sharedSecret, $accessToken);
    $user = User::me();
    
    echo "Successfully authenticated!<br>";
    echo "User ID: {$user->user_id}<br>";
    echo "Primary Email: {$user->primary_email}<br>";
    echo "First Name: {$user->first_name}<br>";
    
} catch (\Etsy\Exception\OAuthException $e) {
    die('OAuth Error: ' . $e->getMessage());
} catch (\Exception $e) {
    die('Error: ' . $e->getMessage());
}
```

## Token Management

### Access Token Lifecycle

- **Validity**: 3600 seconds (1 hour)
- **Usage**: Required for all API requests
- **Format**: Bearer token in Authorization header
- **When expired**: Use refresh token to get a new one

### Refresh Token Lifecycle

- **Validity**: 90 days
- **Usage**: Obtain new access tokens
- **Single use**: Each refresh provides a NEW refresh token
- **Important**: Always store the new refresh token

### Checking Token Expiration

```php
// Store expiration time when obtaining token
$_SESSION['token_expires_at'] = time() + 3600;

// Before making requests, check if expired
function isTokenExpired() {
    return time() >= ($_SESSION['token_expires_at'] ?? 0);
}

if (isTokenExpired()) {
    // Refresh the token
    refreshAccessToken();
}
```

### Refreshing Access Tokens

```php
use Etsy\OAuth\Client;

function refreshAccessToken() {
    global $clientId, $sharedSecret;
    
    $client = new Client($clientId, $sharedSecret);
    
    try {
        [$newAccessToken, $newRefreshToken] = $client->refreshAccessToken(
            $_SESSION['etsy_refresh_token']
        );
        
        // Update stored tokens
        $_SESSION['etsy_access_token'] = $newAccessToken;
        $_SESSION['etsy_refresh_token'] = $newRefreshToken;
        $_SESSION['token_expires_at'] = time() + 3600;
        
        return $newAccessToken;
        
    } catch (\Etsy\Exception\OAuthException $e) {
        // Refresh token expired or invalid
        // User needs to re-authorize
        throw new Exception('Please re-authorize the application.');
    }
}
```

### Token Refresh Wrapper

Automatically refresh tokens when making requests:

```php
function makeAuthenticatedRequest(callable $callback) {
    global $clientId, $sharedSecret;
    
    // Check if token is expired
    if (isTokenExpired()) {
        refreshAccessToken();
    }
    
    // Initialize Etsy with current token
    $etsy = new Etsy(
        $clientId,
        $sharedSecret,
        $_SESSION['etsy_access_token']
    );
    
    // Execute the callback
    try {
        return $callback();
    } catch (\Etsy\Exception\RequestException $e) {
        // If 401 Unauthorized, token might have expired early
        if (strpos($e->getMessage(), '401') !== false) {
            refreshAccessToken();
            return $callback(); // Retry once
        }
        throw $e;
    }
}

// Usage
$listings = makeAuthenticatedRequest(function() {
    return \Etsy\Resources\Listing::all(['limit' => 25]);
});
```

## Permission Scopes

### Available Scopes

The SDK provides all 20 Etsy API scopes:

```php
use Etsy\Utils\PermissionScopes;

// Get all available scopes
$allScopes = PermissionScopes::ALL_SCOPES;
```

| Scope | Access | Description |
|-------|--------|-------------|
| `address_r` | Read | User's addresses |
| `address_w` | Write | User's addresses |
| `billing_r` | Read | Billing information |
| `cart_r` | Read | Shopping cart contents |
| `cart_w` | Write | Shopping cart contents |
| `email_r` | Read | User's email address |
| `favorites_r` | Read | Favorite listings/shops |
| `favorites_w` | Write | Favorite listings/shops |
| `feedback_r` | Read | User feedback/reviews |
| `listings_d` | Delete | Delete listings |
| `listings_r` | Read | Read listings |
| `listings_w` | Write | Create/update listings |
| `profile_r` | Read | User profile information |
| `profile_w` | Write | Update user profile |
| `recommend_r` | Read | Recommendations |
| `recommend_w` | Write | Recommendations |
| `shops_r` | Read | Shop information |
| `shops_w` | Write | Update shop information |
| `transactions_r` | Read | Orders and transactions |
| `transactions_w` | Write | Update orders and transactions |

### Requesting Scopes

```php
// Request specific scopes
$scopes = ['listings_r', 'listings_w', 'shops_r'];

// Or request all scopes (not recommended)
$scopes = \Etsy\Utils\PermissionScopes::ALL_SCOPES;

$authUrl = $client->getAuthorizationUrl(
    $redirectUri,
    $scopes,
    $codeChallenge,
    $nonce
);
```

**Best Practice**: Only request scopes you actually need. Users are more likely to grant permission when they see a minimal scope list.

### Checking Granted Scopes

```php
use Etsy\Etsy;

$etsy = new Etsy($clientId, $sharedSecret, $accessToken);

// Get scopes for the current token
$grantedScopes = $etsy->scopes();

// Check if specific scope is granted
if (in_array('listings_w', $grantedScopes)) {
    // User has granted write access to listings
}
```

## Legacy Token Migration

If you have OAuth 1.0 tokens from Etsy API v2, you can exchange them for OAuth 2.0 tokens.

```php
use Etsy\OAuth\Client;

$client = new Client($clientId, $sharedSecret);

try {
    [$accessToken, $refreshToken] = $client->exchangeLegacyToken(
        $legacyOAuth1Token
    );
    
    // Store the new tokens
    saveTokens($accessToken, $refreshToken);
    
} catch (\Etsy\Exception\OAuthException $e) {
    // Legacy token is invalid or expired
    // User needs to authorize via OAuth 2.0
}
```

**Note**: Legacy tokens must still be valid. If they've expired, users must go through the full OAuth 2.0 flow.

## Security Best Practices

### 1. Store Tokens Securely

**DO**:
- Store in server-side sessions with secure cookies
- Store in encrypted database
- Use environment variables for credentials
- Implement token encryption at rest

**DON'T**:
- Store in cookies (even HTTP-only)
- Store in localStorage/sessionStorage
- Commit tokens to version control
- Log tokens in application logs
- Send tokens in URLs

### 2. Use HTTPS in Production

Always use HTTPS for:
- Redirect URIs
- Token exchange requests
- All API requests

HTTP is only acceptable for localhost development.

### 3. Validate State Parameter

Always verify the returned `state` matches your stored nonce to prevent CSRF attacks.

```php
if ($returnedState !== $storedState) {
    throw new Exception('CSRF protection failed');
}
```

### 4. Implement Token Rotation

Always store the new refresh token when refreshing:

```php
// OLD refresh token becomes invalid after use
[$newAccessToken, $newRefreshToken] = $client->refreshAccessToken($oldRefreshToken);

// MUST store the new refresh token
$_SESSION['etsy_refresh_token'] = $newRefreshToken;
```

### 5. Handle Token Expiration Gracefully

Implement automatic token refresh before expiration:

```php
// Refresh 5 minutes before expiration
$refreshBufferSeconds = 300;

if (time() >= ($_SESSION['token_expires_at'] - $refreshBufferSeconds)) {
    refreshAccessToken();
}
```

### 6. Rate Limit Token Requests

Etsy has rate limits. Don't repeatedly request new tokens unnecessarily.

### 7. Rotate Shared Secret Periodically

Change your app's shared secret periodically and update your code accordingly.

### 8. Log Authentication Failures

Monitor failed authentication attempts to detect potential security issues.

### 9. Use Minimal Scopes

Only request permission scopes your app actually needs.

## Troubleshooting

### "Invalid client ID or shared secret"

**Cause**: Incorrect credentials or missing shared secret.

**Solution**:
- Verify client ID and shared secret from app dashboard
- Ensure you're passing shared secret (required in SDK v1.2.0+)
- Check for extra whitespace in credentials

### "Invalid redirect URI"

**Cause**: Redirect URI doesn't match registered URI.

**Solution**:
- Exact match required (including trailing slashes, HTTP vs HTTPS)
- Check app settings at developers.etsy.com
- Use same URI in authorization URL and token exchange

### "Invalid authorization code"

**Cause**: Code already used, expired, or invalid.

**Solution**:
- Authorization codes are single-use only
- Codes expire after a few minutes
- Don't refresh the callback page
- Start authorization flow again if needed

### "Invalid code verifier"

**Cause**: PKCE verifier doesn't match code challenge.

**Solution**:
- Ensure you're storing verifier correctly between requests
- Use same verifier generated with the challenge
- Don't generate new verifier when exchanging code

### "Token expired"

**Cause**: Access token validity is only 1 hour.

**Solution**:
- Implement automatic token refresh
- Store and check token expiration time
- Use refresh token to get new access token

### "Refresh token expired"

**Cause**: Refresh tokens are valid for 90 days.

**Solution**:
- User must re-authorize your app
- Implement re-authorization flow in your app
- Consider prompting users to re-authorize before expiration

### "State parameter mismatch"

**Cause**: Security issue or session lost between requests.

**Solution**:
- Ensure session is properly started
- Check session storage is working
- Verify nonce generation and storage
- Could indicate CSRF attempt - reject request

## Testing Authentication

### Ping Endpoint

Test your connection without requiring user authorization:

```php
use Etsy\OAuth\Client;

$client = new Client($clientId, $sharedSecret);

$applicationId = $client->ping();

if ($applicationId) {
    echo "Connected! Application ID: {$applicationId}";
} else {
    echo "Connection failed.";
}
```

### Checking Token Scopes

Verify what permissions a token has:

```php
$etsy = new Etsy($clientId, $sharedSecret, $accessToken);
$scopes = $etsy->scopes();

print_r($scopes);
// Output: ['listings_r', 'shops_r', 'transactions_r']
```

## Additional Resources

- [Etsy OAuth Documentation](https://developers.etsy.com/documentation/essentials/authentication)
- [OAuth 2.0 RFC](https://tools.ietf.org/html/rfc6749)
- [PKCE RFC](https://tools.ietf.org/html/rfc7636)
- [Etsy Developer Dashboard](https://www.etsy.com/developers/your-apps)

## Summary

Key takeaways for OAuth 2.0 with this SDK:

1. Always use PKCE (code challenge/verifier)
2. Always validate the state parameter
3. Store tokens securely server-side
4. Implement automatic token refresh
5. Store new refresh token after each refresh
6. Request minimal scopes
7. Use HTTPS in production
8. Handle token expiration gracefully

With proper implementation, your app will provide secure, reliable access to the Etsy API.
