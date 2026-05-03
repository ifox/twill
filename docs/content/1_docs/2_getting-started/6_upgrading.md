# Upgrading from Twill 3.x to 4.0

Twill 4 raises the platform baseline, removes several previously bundled integrations, and finalizes a number of
deprecated APIs.

## Platform requirements

- PHP `8.2+`
- Laravel `11.x`, `12.x`, or `13.x`
- Node.js with Vite-compatible tooling for frontend builds

## Composer changes

Remove dependencies that Twill 4 no longer requires by default:

```bash
composer remove myclabs/php-enum doctrine/dbal laravel/ui guzzlehttp/guzzle spatie/once cartalyst/tags
```

Install optional integrations only when your project uses them:

```bash
composer require laravel/socialite
composer require spatie/laravel-analytics
composer require imgix/imgix-php
composer require league/flysystem-aws-s3-v3
composer require matthewbdaly/laravel-azure-storage
```

## Removed and optional dependencies

| Dependency | Twill 4 status | Replacement |
| --- | --- | --- |
| `myclabs/php-enum` | Removed | Native PHP enums |
| `doctrine/dbal` | Removed | Laravel 11 native column modification support |
| `laravel/ui` | Removed | Internal Twill auth traits |
| `guzzlehttp/guzzle` | Removed | Laravel `Http` facade |
| `spatie/once` | Removed | Laravel native `once()` helper |
| `cartalyst/tags` | Removed | Internal Twill tag models and trait |
| `spatie/laravel-analytics` | Optional | Install only for dashboard analytics |
| `laravel/socialite` | Optional | Install only for OAuth login |
| `imgix/imgix-php` | Optional | Install only for Imgix media rendering |
| `league/flysystem-aws-s3-v3` | Optional | Install only for S3-backed media or files |
| `matthewbdaly/laravel-azure-storage` | Optional | Install only for Azure-backed media or files |

## Configuration updates

### Before

```php
'enabled' => [
    'users-oauth' => true,
],
'media_library' => [
    'endpoint_type' => 's3',
    'image_service' => 'A17\Twill\Services\MediaLibrary\Imgix',
],
```

### After

```php
'enabled' => [
    'users-oauth' => true,
],
'media_library' => [
    'endpoint_type' => env('MEDIA_LIBRARY_ENDPOINT_TYPE', 's3'),
    'image_service' => env('MEDIA_LIBRARY_IMAGE_SERVICE', 'A17\Twill\Services\MediaLibrary\Imgix'),
],
```

And in `.env`:

```bash
MEDIA_LIBRARY_ENDPOINT_TYPE=s3
MEDIA_LIBRARY_IMAGE_SERVICE="A17\Twill\Services\MediaLibrary\Imgix"
S3_KEY=
S3_SECRET=
S3_BUCKET=
S3_REGION=
IMGIX_SOURCE_HOST=
```

## Deprecated API cleanup

Twill 4 removes deprecated compatibility layers and expects applications to use the current controller/repository APIs.
Audit any overrides in your application, especially:

- legacy Blade directive usage,
- old repository method signatures,
- `ModuleController` compatibility shims left over from Twill 2.x and 3.x.

## Frontend changes

Twill 4 upgrades the admin frontend to Vue 3 and Vite.

- `vue.config.js` customizations must be ported to `vite.config.js`
- Vue 2 slot syntax must be updated to Vue 3 syntax
- Vue filters should be replaced with computed properties or methods
- `beforeDestroy` becomes `beforeUnmount`
- `$scopedSlots` becomes `$slots`
- Vue 3 `v-model` argument syntax should be used where applicable

## Quill removal

Quill is no longer bundled. Tiptap is the supported WYSIWYG editor in Twill 4. Any custom Quill integrations or CSS
hooks should be migrated to your Tiptap extensions, toolbar configuration, and editor views.
