# Getting started

## Environment requirements

Twill `4.x` targets Laravel `11.x`, `12.x`, and `13.x` running on PHP `8.2` and above. As a dependency of your own
application, Twill shares Laravel's
[server requirements](https://laravel.com/docs/12.x/deployment#server-requirements).

## Development

For development, those requirements are satisfied by the following first-party solutions:

- [Herd](https://herd.laravel.com) (macOS and Windows)
- [Valet](https://laravel.com/docs/12.x/valet) (macOS)
- [Sail](https://laravel.com/docs/12.x/sail) (All platforms)
- [Homestead](https://laravel.com/docs/12.x/homestead) (All platforms)

## Production

For production deployments, we can recommend:

- [Forge](https://forge.laravel.com)
- [Vapor](https://vapor.laravel.com)
- [Envoyer](https://envoyer.io)
- [Envoy](https://laravel.com/docs/12.x/envoy)
- [Deployer](https://deployer.org/)

Of course, any other Laravel compatible server configuration and deployment strategy will be supported.

## Frontend assets

Twill 4 uses Vue 3 and Vite to build the frontend assets of its UI. The provided npm scripts cover development,
watch mode, and production builds.

## Database

Twill's database migrations create `json` columns. Your database should support the `json` type. Twill has been
developed and tested against MySQL (`>=5.7`) and PostgreSQL(`>=9.3`) databases.

## Summary

|            | Supported versions | Recommended version |
|:-----------|:------------------:|:-------------------:|
| PHP        |       >= 8.2       |         8.4         |
| Laravel    |       >= 11.0      |        12.x         |
| Node.js    |       >= 16        |        20.x         |
| MySQL      |       >= 5.7       |         8.x         |
| PostgreSQL |       >= 9.3       |        15.x         |

## What you'll build

The `basic-page-builder` preset gives you a working Twill installation with:

- a pages module,
- a navigation module,
- a homepage setting,
- Blade-rendered frontend pages,
- and example content blocks powered by Vite.
