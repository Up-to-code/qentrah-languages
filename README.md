# Qentrah Languages — 0.1.0 preview

**Own your languages. Edit in WordPress.** A standalone, local-first WordPress multilingual plugin by [Qentrah](https://www.qentrah.com/).

[Download plugin ZIP](https://github.com/Up-to-code/qentrah-languages/releases/tag/v0.1.0) · [User guide](docs/user-guide.md) · [Developer guide](docs/developer-guide.md) · [Deployment](docs/deployment.md) · [Competitor research](docs/market-research.md)

The plugin is independent of the [Dentora Production theme](https://github.com/Up-to-code/dentoralab-wordpress-production). Their shared product references live in the [original design repository](https://github.com/Up-to-code/dentoralab-clone/blob/main/qentrah-family.json). This plugin's implementation is not copied from WPML, Polylang, TranslatePress or Weglot.

## Included

- Add languages and RTL/LTR direction without code changes.
- Open the matching translation from the native editor; save first to avoid losing work.
- Create draft translations or link existing pages.
- Keep normal WordPress posts, media and revisions in your own database.
- Published-only switcher and reciprocal hreflang; source-change review notices.
- Shortcode/block integration, explicit archive-language query filtering and relationship export.
- PHP/integration tests, ZIP builds on push and gated WordPress.org deployment for releases.

**Preview scope:** human-authored translations, native posts/pages and blocks. It does not provide machine translation, taxonomy synchronization, WooCommerce integration, multisite/domain routing or certified compatibility with every page builder. Keep it separate from other multilingual plugins unless you have tested the combination.

## Install

Upload `qentrah-languages.zip` under Plugins → Add New → Upload Plugin. Activate, then open Settings → Qentrah Languages. The ZIP is independent of any theme. See [user guide](docs/user-guide.md).

## Build and verify

```sh
python3 tools/package.py
php -l qentrah-languages.php
wp --user=ADMIN eval-file tests/integration.php
```

The integration test creates and removes fixtures and restores language settings. Run on a development/staging site. Every push builds an artifact; tagged releases attach installable ZIPs. Public WordPress.org publication is not yet approved; see the deployment prerequisites.

## Brand and license

Original plugin code: GPL-2.0-or-later. `assets/logo.png` was generated for Qentrah Languages with the built-in image-generation tool; [prompt and usage notes](docs/brand.md). WordPress.org acceptance, trademark clearance and broad third-party compatibility are not implied by the logo or repository name.
