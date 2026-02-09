# Troubleshooting

Common issues and solutions when using the Etsy PHP SDK.

## Table of Contents

- [Authentication Issues](#authentication-issues)
- [API Errors](#api-errors)
- [SDK Errors](#sdk-errors)
- [Connection Issues](#connection-issues)
- [Performance Issues](#performance-issues)
- [Data Issues](#data-issues)
- [Debugging Tips](#debugging-tips)

## Authentication Issues

### "Invalid client ID or shared secret"

**Symptoms**: OAuthException when initializing Client

**Causes**:
- Incorrect credentials
- Missing shared secret (required in v1.2.0+)
- Extra whitespace in credentials

**Solutions**:

```php
// Check credentials from app dashboard
$clientId = 'your_client_id_here';  // No extra spaces
$sharedSecret = 'your_shared_secret_here';  // Required in v1.2.0+

// Trim whitespace
$clientId = trim($clientId);
$sharedSecret = trim($sharedSecret);

$client = new Client($clientId, $sharedSecret);
```

### "Access token expired" or "Unauthorized"

**Symptoms**: 401 errors on API requests

**Causes**:
- Access token expired (1 hour validity)
- Token not set correctly
- Token for different user/app

**Solutions**:

```php
// Check token expiration
if (time() >= $_SESSION['token_expires_at']) {
    // Refresh token
    $client = new Client($clientId, $sharedSecret);
    [$newAccessToken, $newRefreshToken] = $client->refreshAccessToken(
        $_SESSION['etsy_refresh_token']
    );
    
    $_SESSION['etsy_access_token'] = $newAccessToken;
    $_SESSION['etsy_refresh_token'] = $newRefreshToken;
    $_SESSION['token_expires_at'] = time() + 3600;
}

// Reinitialize Etsy with new token
$etsy = new Etsy($clientId, $sharedSecret, $_SESSION['etsy_access_token']);
```

### "Invalid state parameter"

**Symptoms**: State mismatch during OAuth callback

**Causes**:
- Session lost between requests
- CSRF attempt
- Multiple authorization flows running

**Solutions**:

```php
// Ensure session is started
session_start();

// Generate and store nonce
$nonce = $client->createNonce();
$_SESSION['oauth_nonce'] = $nonce;
$_SESSION['created_at'] = time();

// In callback:
if (empty($_SESSION['oauth_nonce'])) {
    die('Session expired. Please authorize again.');
}

// Add timeout check
if (time() - $_SESSION['created_at'] > 600) {  // 10 minutes
    die('Authorization timed out. Please try again.');
}

if ($_GET['state'] !== $_SESSION['oauth_nonce']) {
    die('Invalid state parameter');
}
```

### "Refresh token expired"

**Symptoms**: Can't refresh access token (90-day limit)

**Causes**:
- Refresh token not used for 90 days
- User revoked access

**Solutions**:

```php
try {
    [$accessToken, $refreshToken] = $client->refreshAccessToken($refreshToken);
} catch (OAuthException $e) {
    // Refresh token expired - need to re-authorize
    header('Location: /authorize.php');
    exit;
}
```

## API Errors

### 404 Not Found

**Symptoms**: Resource not found

**Causes**:
- Invalid ID
- Resource deleted
- Wrong shop context

**Solutions**:

```php
// Always check for null
$listing = Listing::get($listingId);

if (!$listing) {
    echo "Listing not found\n";
    // Handle gracefully
}

// Or enable exceptions
$etsy = new Etsy($clientId, $sharedSecret, $accessToken, [
    '404_error' => true
]);

try {
    $listing = Listing::get($listingId);
} catch (RequestException $e) {
    echo "Error: " . $e->getMessage();
}
```

### 403 Forbidden / Insufficient Permissions

**Symptoms**: Permission denied errors

**Causes**:
- Missing required scope
- Accessing another user's resources
- Shop not owned by user

**Solutions**:

```php
// Check granted scopes
$etsy = new Etsy($clientId, $sharedSecret, $accessToken);
$scopes = $etsy->scopes();

if (!in_array('listings_w', $scopes)) {
    echo "Missing 'listings_w' scope. Please re-authorize.\n";
    // Redirect to authorization with required scopes
}

// Verify shop ownership
$user = User::me();
$shop = $user->shop();

if ($shop->shop_id !== $requestedShopId) {
    die("You don't own this shop");
}
```

### 429 Rate Limit Exceeded

**Symptoms**: Too many requests error

**Causes**:
- Exceeding Etsy's rate limits
- No delays between requests
- Bulk operations too fast

**Solutions**:

```php
// Add delays between requests
function rateLimitedRequest(callable $callback, $delayMs = 500) {
    static $lastRequest = 0;
    
    $elapsed = (microtime(true) - $lastRequest) * 1000;
    if ($elapsed < $delayMs) {
        usleep(($delayMs - $elapsed) * 1000);
    }
    
    $result = $callback();
    $lastRequest = microtime(true);
    
    return $result;
}

// Usage
$listing = rateLimitedRequest(function() use ($listingId) {
    return Listing::get($listingId);
});

// For bulk operations
foreach ($listings as $listing) {
    $listing->save();
    usleep(500000);  // 0.5 second delay
}
```

### 400 Bad Request / Validation Errors

**Symptoms**: Invalid parameters error

**Causes**:
- Missing required fields
- Invalid field values
- Wrong data types

**Solutions**:

```php
// Validate data before sending
function validateListingData($data) {
    $required = ['quantity', 'title', 'description', 'price', 'who_made', 'when_made', 'taxonomy_id'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Validate title length
    if (strlen($data['title']) > 140) {
        throw new Exception("Title too long (max 140 characters)");
    }
    
    // Validate tags
    if (isset($data['tags']) && count($data['tags']) > 13) {
        throw new Exception("Too many tags (max 13)");
    }
    
    return $data;
}

try {
    $validData = validateListingData($listingData);
    $listing = Listing::create($shopId, $validData);
} catch (Exception $e) {
    echo "Validation error: " . $e->getMessage();
}
```

## SDK Errors

### "The X resource does not support pagination"

**Symptoms**: SdkException when calling `paginate()`

**Causes**:
- Resource doesn't support pagination
- Only Listing, Shop, and Review support it

**Solutions**:

```php
// Check if pagination is supported
$supportedResources = ['Listing', 'Shop', 'Review'];

if (in_array($resourceType, $supportedResources)) {
    foreach ($collection->paginate(200) as $item) {
        // ...
    }
} else {
    // Manual pagination
    $offset = 0;
    $limit = 25;
    
    do {
        $page = Resource::all($params + ['offset' => $offset, 'limit' => $limit]);
        foreach ($page->data as $item) {
            // Process item
        }
        $offset += $limit;
    } while ($page->count() == $limit);
}
```

### "Etsy class not initialized"

**Symptoms**: SDK methods not working

**Causes**:
- Forgot to initialize Etsy
- Trying to use resources before initialization

**Solutions**:

```php
// ALWAYS initialize first
$etsy = new Etsy($clientId, $sharedSecret, $accessToken);

// THEN use resources
$user = User::me();
$listings = Listing::all();
```

### "Call to undefined method"

**Symptoms**: Method doesn't exist

**Causes**:
- Typo in method name
- Using wrong resource
- Method doesn't exist

**Solutions**:

```php
// Check API reference for correct method names
// https://github.com/rhysnhall/etsy-php-sdk

// Common mistakes:
// - Listing::getAll()  // Wrong
Listing::all();         // Correct

// - $listing->getImages()  // Wrong
$listing->images();          // Correct
```

## Connection Issues

### "Connection timed out"

**Symptoms**: Request takes too long

**Causes**:
- Network issues
- Large file uploads
- Slow internet connection

**Solutions**:

```php
// Increase timeouts
set_time_limit(300);  // 5 minutes
ini_set('max_execution_time', 300);

// For uploads, optimize first
function optimizeImage($path, $maxWidth = 2000) {
    // Use image library to resize/compress
    // ...
}

$optimizedPath = './temp/optimized.jpg';
optimizeImage('./original.jpg', $optimizedPath);
$listing->uploadImage($optimizedPath, ['rank' => 1]);
unlink($optimizedPath);
```

### "SSL certificate problem"

**Symptoms**: SSL verification errors

**Causes**:
- Outdated CA certificates
- Corporate proxy
- Development environment issues

**Solutions**:

```php
// Update CA certificates (best solution)
// Linux: sudo apt-get install ca-certificates
// Or update php.ini: openssl.cafile="/path/to/ca-bundle.crt"

// For development ONLY (not production):
// Disable SSL verification (not recommended)
// This would require extending the Client class
```

## Performance Issues

### "Memory limit exceeded"

**Symptoms**: PHP runs out of memory

**Causes**:
- Loading too much data at once
- Not using pagination
- Large file operations

**Solutions**:

```php
// Increase memory limit
ini_set('memory_limit', '256M');

// Use pagination (memory efficient)
foreach ($listings->paginate(500) as $listing) {
    processListing($listing);
    // Each listing is garbage collected after processing
}

// Don't load all into array
// Bad:
$allListings = iterator_to_array($listings->paginate(500));

// For file uploads, stream instead of loading entire file
$handle = fopen('./large-file.jpg', 'r');
// ... upload ...
fclose($handle);
```

### "Script execution time exceeded"

**Symptoms**: Script times out

**Causes**:
- Too many API requests
- Large batch operations
- No rate limiting

**Solutions**:

```php
// Process in batches
function processBatch($items, $batchSize = 50) {
    $batches = array_chunk($items, $batchSize);
    
    foreach ($batches as $index => $batch) {
        echo "Processing batch " . ($index + 1) . "...\n";
        
        foreach ($batch as $item) {
            processItem($item);
            usleep(300000);  // Rate limiting
        }
        
        // Optional: save progress
        file_put_contents('./progress.txt', $index + 1);
    }
}

// Resume from failure
$startBatch = (int)file_get_contents('./progress.txt');
$batches = array_chunk($items, 50);
$batches = array_slice($batches, $startBatch);
```

## Data Issues

### "Property doesn't exist" or returns null

**Symptoms**: Expected property is missing/null

**Causes**:
- Property not included in API response
- Misspelled property name
- Association not included

**Solutions**:

```php
// Check what properties exist
$listing = Listing::get($listingId);
print_r($listing->toArray());

// Include associations
$listing = Listing::get($listingId, [
    'includes' => ['Shop', 'Images', 'Videos']
]);

// Properties are case-insensitive
$listing->shop_id === $listing->Shop_Id === $listing->SHOP_ID

// Check for null before using
$shop = $listing->shop;
if ($shop) {
    echo $shop->shop_name;
} else {
    echo "Shop not included in response";
}
```

### "Unexpected data format"

**Symptoms**: Data not in expected format

**Causes**:
- API response changed
- Incorrect assumptions
- Wrong data type

**Solutions**:

```php
// Always check data types
$listing = Listing::get($listingId);

// Price is an object, not a float
// Wrong:
// $price = $listing->price;

// Correct:
$price = $listing->price->amount / 100;  // Convert cents to dollars
$currency = $listing->price->currency_code;

// Tags might be null or array
$tags = $listing->tags ?? [];

// Materials might be null or array
$materials = $listing->materials ?? [];
```

## Debugging Tips

### Enable Error Reporting

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Log API Requests

```php
// Create a wrapper for logging
function loggedRequest($method, ...$args) {
    $start = microtime(true);
    
    try {
        $result = Etsy::$client->$method(...$args);
        $duration = microtime(true) - $start;
        
        error_log(sprintf(
            "[API] %s %s - %.2fs - Success",
            strtoupper($method),
            $args[0],
            $duration
        ));
        
        return $result;
    } catch (Exception $e) {
        $duration = microtime(true) - $start;
        
        error_log(sprintf(
            "[API] %s %s - %.2fs - Error: %s",
            strtoupper($method),
            $args[0],
            $duration,
            $e->getMessage()
        ));
        
        throw $e;
    }
}
```

### Inspect Raw Responses

```php
// Make direct request to see raw response
$response = Etsy::$client->get("/application/listings/{$listingId}");

// Inspect
echo json_encode($response, JSON_PRETTY_PRINT);
```

### Check SDK Version

```php
// In composer.json
// "rhysnhall/etsy-php-sdk": "^1.2.0"

// Or check installed version
composer show rhysnhall/etsy-php-sdk
```

### Test with Ping

```php
$client = new Client($clientId, $sharedSecret);
$appId = $client->ping();

if ($appId) {
    echo "Connection successful. App ID: {$appId}\n";
} else {
    echo "Connection failed\n";
}
```

### Verify Permissions

```php
$etsy = new Etsy($clientId, $sharedSecret, $accessToken);
$scopes = $etsy->scopes();

echo "Granted scopes:\n";
foreach ($scopes as $scope) {
    echo "  - {$scope}\n";
}
```

### Check Resource State

```php
$listing = Listing::get($listingId);

// View all properties
var_dump($listing->toArray());

// Check specific properties
echo "State: {$listing->state}\n";
echo "Quantity: {$listing->quantity}\n";
echo "Price: {$listing->price->amount}\n";
```

## Getting Help

If you're still stuck:

1. **Check the Examples**: [EXAMPLES.md](EXAMPLES.md)
2. **Read the API Reference**: [API_REFERENCE.md](API_REFERENCE.md)
3. **Etsy API Docs**: [`https://developers.etsy.com/documentation/reference`](https://developers.etsy.com/documentation/reference)
4. **GitHub Issues**: [`https://github.com/rhysnhall/etsy-php-sdk/issues`](https://github.com/rhysnhall/etsy-php-sdk/issues)
5. **Contact Author**: hello@rhyshall.com

When reporting issues, include:
- SDK version
- PHP version
- Error message (full stack trace)
- Code sample demonstrating the issue
- Expected vs actual behavior

## Summary

Common issue categories:

1. **Authentication**: Token management, OAuth flow
2. **API Errors**: 404, 403, 429, 400 responses
3. **SDK Errors**: Pagination, initialization, method calls
4. **Connection**: Timeouts, SSL, network issues
5. **Performance**: Memory, execution time, rate limits
6. **Data**: Property access, formats, associations

Key debugging steps:

1. Enable error reporting
2. Check SDK version
3. Test connection with ping
4. Verify permissions
5. Log API requests
6. Inspect raw responses
7. Check documentation

Most issues can be resolved by:
- Reading error messages carefully
- Checking the API reference
- Validating data before sending
- Implementing proper error handling
- Adding rate limiting and delays
