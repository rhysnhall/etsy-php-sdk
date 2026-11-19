# Local test environment

Scripts for checking the SDK against the live Etsy API with your own app and shop.
Run every command from the repository root. This folder isn't included in the Packagist package.

Your keys and tokens live in `dev/.env`, which git ignores.

## First-time setup

1. Install the dependencies: `composer install`
2. Copy `dev/.env.example` to `dev/.env`, or run any script once and it will be created for you.
3. Paste your app's **keystring** and **shared secret** from [Your Apps](https://www.etsy.com/developers/your-apps) into `dev/.env`.
4. On the same Your Apps page, add `http://localhost:3003/oauth/redirect` as a callback URL for your app.
5. Start the login page: `php -S localhost:3003 dev/auth.php`
6. Open http://localhost:3003, click **Connect to Etsy** and approve access. Your tokens, user ID and shop ID are saved to `dev/.env`.
7. Stop the server with Ctrl+C.

## Running the checks

```
php dev/check.php                        # read-only (default)
php dev/check.php --write                # also tests writes on a temporary draft listing
php dev/check.php --write --keep-draft   # leaves the draft so you can inspect it
php dev/check.php --refresh              # force a token refresh
```

**Read-only checks** don't change anything in your shop. They check:
- your keys and token
- `Listing::allByIds()` with both ID formats
- the batch inventory and shipping methods
- `EconomicOperator::all()`

They use two of your active listings. To use other public listings instead, set `TEST_LISTING_IDS`.

**`--write`** creates a draft listing, which buyers can't see and Etsy doesn't charge for. It updates the draft's inventory and personalization (both sent as JSON), then deletes the draft. It needs the `listings_w` and `listings_d` scopes and at least one shipping profile in your shop. If the delete fails, the script prints the draft's listing ID so you can remove it from Shop Manager.

## Regenerating the resource reference

`docs/resources.md` is generated from the SDK source and Etsy's current OpenAPI spec. After adding or changing resource methods, run:

```
php dev/generate-resource-docs.php
```

Descriptions and groupings are set in the script. It warns about any endpoint the SDK calls that's missing from Etsy's spec, and stops if a new resource class hasn't been added to its list.

## Updating the endpoint list

`tests/EndpointsTest.php` calls every resource method and checks each request it makes is an endpoint in Etsy's API spec. The endpoints are saved in `tests/fixtures/endpoints.json`, so the tests don't need a network connection. When Etsy changes the API, rebuild the list and run the tests:

```
php dev/update-endpoints.php
composer test
```

## Rotating keys

- **New keystring or shared secret:** paste them into `dev/.env`, then reconnect (setup steps 5 and 6). Running `php dev/check.php` first confirms the new keys work.
- **New tokens only:** reconnect (setup steps 5 and 6), or paste `ETSY_ACCESS_TOKEN` and `ETSY_REFRESH_TOKEN` directly into `dev/.env`.
- **Different scopes:** edit `ETSY_SCOPES`, then reconnect.

Access tokens expire after an hour. The scripts refresh them automatically using the refresh token, which lasts 90 days.
