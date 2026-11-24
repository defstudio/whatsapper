# Changelog

All notable changes to `whatsapper` will be documented in this file.

## v2.0.3 - 2026-11-24

**Full Changelog**: https://github.com/defstudio/whatsapper/compare/v2.0.2...v2.0.3

## Unreleased

### Security

- Require a Meta App Secret when enabling webhooks and validate POST signatures
  against the raw request body before processing any payload.
- Resolve image, video, and audio download URLs through Graph API media metadata;
  ignore webhook URLs, validate HTTPS Meta destinations, and disable redirects.
- Reject unsafe media identifiers, use the configured filesystem for automatic
  storage, encode payload filename identifiers, and restrict media extensions.
- Add a security policy and link to private vulnerability reporting.

### Upgrade requirement

Configure `WHATSAPPER_APP_SECRET` (including the `webhook.app_secret` entry in
previously published configuration), or pass `appSecret` to `enableWebhook()`.
The GET verification token cannot replace the Meta App Secret. See the README
for configuration cache and compatibility details.

## v2.0.2 - 2026-11-18

### What's Changed

- **Validate WhatsApp reply context before building** by fabio-ivona
- **Update CHANGELOG** by fabio-ivona

**Full Changelog**: https://github.com/defstudio/whatsapper/compare/v2.0.1...v2.0.2

## v2.0.1 - 2026-07-15

### What's Changed

* update for laravel 13 by @MarioGattolla in https://github.com/defstudio/whatsapper/pull/3

### New Contributors

* @MarioGattolla made their first contribution in https://github.com/defstudio/whatsapper/pull/3

**Full Changelog**: https://github.com/defstudio/whatsapper/compare/v2.0.0...v2.0.1

## v2.0.0 - 2026-04-29

**Full Changelog**: https://github.com/defstudio/whatsapper/commits/v2.0.0
