<?php
/**
 * Rebuilds tests/fixtures/endpoints.json, the list of Etsy API operations the
 * test suite checks every resource method against. Run it when Etsy changes
 * the API:
 *
 *   php dev/update-endpoints.php              Downloads Etsy's OpenAPI spec.
 *   php dev/update-endpoints.php spec.json    Uses a local copy instead.
 */

const SPEC_URL = 'https://www.etsy.com/openapi/generated/oas/3.0.0.json';

$source = $argv[1] ?? SPEC_URL;
$json = @file_get_contents($source);
$spec = $json ? json_decode($json, true) : null;
if(!isset($spec['paths'])) {
  fwrite(STDERR, "Couldn't read an OpenAPI spec from {$source}" . PHP_EOL);
  exit(1);
}

$operations = [];
foreach($spec['paths'] as $path => $methods) {
  foreach($methods as $method => $operation) {
    if(!isset($operation['operationId'])) {
      continue;
    }
    $operations[$operation['operationId']] = strtoupper($method) . ' ' . preg_replace('#^/v3#', '', $path);
  }
}
ksort($operations);

$file = dirname(__DIR__) . '/tests/fixtures/endpoints.json';
file_put_contents($file, json_encode($operations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
echo 'Wrote ' . count($operations) . " operations to tests/fixtures/endpoints.json" . PHP_EOL;
