<?php
/**
 * Shared helpers for the local Etsy test environment.
 * Not part of the SDK; excluded from the Packagist dist via .gitattributes.
 */

require __DIR__ . '/../vendor/autoload.php';

use Etsy\Etsy;
use Etsy\OAuth\Client;

const DEV_ENV_FILE = __DIR__ . '/.env';
const DEV_ENV_EXAMPLE = __DIR__ . '/.env.example';
// Etsy access tokens last one hour. Refresh a little early.
const DEV_TOKEN_LIFETIME = 3600;
const DEV_REFRESH_MARGIN = 120;

/**
 * Load dev/.env as an associative array, creating it from the example if needed.
 */
function devEnv(): array {
  if(!file_exists(DEV_ENV_FILE)) {
    copy(DEV_ENV_EXAMPLE, DEV_ENV_FILE);
  }
  $env = [];
  foreach(file(DEV_ENV_FILE, FILE_IGNORE_NEW_LINES) as $line) {
    $line = trim($line);
    if($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
      continue;
    }
    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim(trim($value), "\"'");
  }
  return $env;
}

/**
 * Update keys in dev/.env in place, keeping comments and ordering.
 */
function devEnvSave(array $values): void {
  $lines = file_exists(DEV_ENV_FILE) ? file(DEV_ENV_FILE, FILE_IGNORE_NEW_LINES) : [];
  foreach($values as $key => $value) {
    $found = false;
    foreach($lines as $i => $line) {
      if(preg_match('/^\s*' . preg_quote($key, '/') . '\s*=/', $line)) {
        $lines[$i] = "{$key}={$value}";
        $found = true;
        break;
      }
    }
    if(!$found) {
      $lines[] = "{$key}={$value}";
    }
  }
  file_put_contents(DEV_ENV_FILE, implode(PHP_EOL, $lines) . PHP_EOL);
}

/**
 * Fail with a helpful message when the app keys are missing.
 */
function devRequireKeys(array $env): void {
  if(empty($env['ETSY_KEYSTRING']) || empty($env['ETSY_SHARED_SECRET'])) {
    throw new RuntimeException(
      "ETSY_KEYSTRING and ETSY_SHARED_SECRET must be set in dev/.env."
    );
  }
}

/**
 * An OAuth client for the configured app.
 */
function devOAuthClient(array $env): Client {
  devRequireKeys($env);
  return new Client($env['ETSY_KEYSTRING'], $env['ETSY_SHARED_SECRET']);
}

/**
 * Save a token pair returned by the OAuth client.
 */
function devSaveTokens(array $tokens): void {
  devEnvSave([
    'ETSY_ACCESS_TOKEN' => $tokens['access_token'],
    'ETSY_REFRESH_TOKEN' => $tokens['refresh_token'],
    'ETSY_ACCESS_TOKEN_EXPIRES' => time() + DEV_TOKEN_LIFETIME,
  ]);
}

/**
 * Refresh the access token if it has expired (or will shortly). Returns true
 * when a refresh happened.
 */
function devRefreshIfNeeded(array &$env, bool $force = false): bool {
  $expires = (int)($env['ETSY_ACCESS_TOKEN_EXPIRES'] ?? 0);
  if(!$force && !empty($env['ETSY_ACCESS_TOKEN']) && $expires > time() + DEV_REFRESH_MARGIN) {
    return false;
  }
  if(empty($env['ETSY_REFRESH_TOKEN'])) {
    if(!empty($env['ETSY_ACCESS_TOKEN']) && !$expires) {
      // A pasted access token with unknown expiry. Use it as is.
      return false;
    }
    throw new RuntimeException(
      "No valid access token. Run `php -S localhost:3003 dev/auth.php` and connect your shop."
    );
  }
  devSaveTokens(devOAuthClient($env)->refreshAccessToken($env['ETSY_REFRESH_TOKEN']));
  $env = devEnv();
  return true;
}

/**
 * Boot the SDK with an authenticated client. 404s throw so tests fail loudly.
 */
function devEtsy(array $env): Etsy {
  devRequireKeys($env);
  return new Etsy(
    $env['ETSY_KEYSTRING'],
    $env['ETSY_SHARED_SECRET'],
    $env['ETSY_ACCESS_TOKEN'] ?: null,
    ['404_error' => true]
  );
}

/**
 * Show the first and last few characters of a secret.
 */
function devMask(?string $value): string {
  if(!$value) {
    return '(not set)';
  }
  return strlen($value) <= 10 ? str_repeat('*', strlen($value)) : substr($value, 0, 4) . '...' . substr($value, -4);
}
