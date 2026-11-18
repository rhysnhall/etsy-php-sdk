<?php
/**
 * Local OAuth helper. Run from the repo root:
 *
 *   php -S localhost:3003 dev/auth.php
 *
 * then open http://localhost:3003 and connect your shop. Tokens are saved to dev/.env.
 */

require __DIR__ . '/lib.php';

use Etsy\Resources\User;

const DEV_STATE_FILE = __DIR__ . '/.auth-state.json';

function page(string $title, string $body): void {
  $title = htmlspecialchars($title);
  echo <<<HTML
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title}</title>
<style>
  body { font: 16px/1.5 system-ui, sans-serif; max-width: 640px; margin: 48px auto; padding: 0 16px; color: #222; }
  code { background: #f2f2f2; padding: 2px 6px; border-radius: 4px; }
  .btn { display: inline-block; background: #f1641e; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none; }
  .ok { color: #1a7f37; } .err { color: #b42318; }
  td { padding: 2px 12px 2px 0; }
</style></head><body><h1>{$title}</h1>{$body}</body></html>
HTML;
}

$h = fn($v) => htmlspecialchars((string)$v);

try {
  $env = devEnv();
  $client = devOAuthClient($env);
  $redirect = $env['ETSY_REDIRECT_URI'] ?: 'http://localhost:3003/oauth/redirect';
  $callbackPath = parse_url($redirect, PHP_URL_PATH) ?: '/oauth/redirect';
  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $scopes = preg_split('/\s+/', trim($env['ETSY_SCOPES'] ?? 'listings_r shops_r'));

  if($path === '/favicon.ico') {
    http_response_code(404);
    return;
  }

  if($path === '/connect') {
    [$verifier, $challenge] = $client->generateChallengeCode();
    $state = $client->createNonce();
    file_put_contents(DEV_STATE_FILE, json_encode(['state' => $state, 'verifier' => $verifier]));
    header('Location: ' . $client->getAuthorizationUrl($redirect, $scopes, $challenge, $state));
    return;
  }

  if($path === $callbackPath) {
    if(isset($_GET['error'])) {
      throw new RuntimeException("Etsy returned an error: {$_GET['error']} " . ($_GET['error_description'] ?? ''));
    }
    $saved = json_decode(@file_get_contents(DEV_STATE_FILE) ?: '{}', true);
    @unlink(DEV_STATE_FILE);
    if(empty($saved['state']) || !hash_equals($saved['state'], (string)($_GET['state'] ?? ''))) {
      throw new RuntimeException('State mismatch. Start again from the home page.');
    }
    devSaveTokens($client->requestAccessToken($redirect, $_GET['code'] ?? '', $saved['verifier']));
    $env = devEnv();
    devEtsy($env);
    $me = User::me();
    devEnvSave([
      'ETSY_USER_ID' => $me->user_id ?? '',
      'ETSY_SHOP_ID' => $me->shop_id ?? '',
    ]);
    page('Connected', '<p class="ok">Tokens saved to <code>dev/.env</code>.</p>'
      . '<table><tr><td>User ID</td><td>' . $h($me->user_id ?? '-') . '</td></tr>'
      . '<tr><td>Shop ID</td><td>' . $h($me->shop_id ?? '-') . '</td></tr>'
      . '<tr><td>Scopes</td><td>' . $h(implode(' ', $scopes)) . '</td></tr></table>'
      . '<p>You can stop the server (Ctrl+C) and run <code>php dev/check.php</code>.</p>');
    return;
  }

  page('Etsy SDK test environment',
    '<table>'
    . '<tr><td>Keystring</td><td><code>' . $h(devMask($env['ETSY_KEYSTRING'])) . '</code></td></tr>'
    . '<tr><td>Redirect URI</td><td><code>' . $h($redirect) . '</code></td></tr>'
    . '<tr><td>Scopes</td><td><code>' . $h(implode(' ', $scopes)) . '</code></td></tr>'
    . '<tr><td>Current token</td><td><code>' . $h(devMask($env['ETSY_ACCESS_TOKEN'] ?? '')) . '</code></td></tr>'
    . '</table>'
    . '<p>The redirect URI must be listed as a callback URL on your app at '
    . '<a href="https://www.etsy.com/developers/your-apps">Your Apps</a>.</p>'
    . '<p><a class="btn" href="/connect">Connect to Etsy</a></p>');
}
catch(Throwable $e) {
  http_response_code(500);
  page('Something went wrong', '<p class="err">' . $h($e->getMessage()) . '</p><p><a href="/">Start again</a></p>');
}
