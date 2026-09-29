=== Qentrah Languages ===
Contributors: up-to-code
Tags: multilingual, translation, language switcher, rtl, block editor
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 0.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Local-first multilingual publishing with native WordPress editor navigation and linked translations.

== Description ==
Own your languages. Edit in WordPress.

Qentrah Languages links normal WordPress posts and pages into translation groups. Add languages, open the matching translation directly from the editor, or create a draft copy for a translator. Authored content stays in your WordPress database. There are no required accounts, remote translation calls, tracking, or subscriptions.

* Configurable language names, locale codes and text direction.
* Native block editor Languages panel and classic language metabox.
* Create draft translations or link existing pages.
* Source-change review notice.
* Language switcher block and [qentrah_languages] shortcode.
* Published-only hreflang links and HTML language/direction.
* Relationship export; content and metadata retained on uninstall.

This first release is intended for editorial posts/pages and native blocks. It does not automatically translate text. WooCommerce, multisite domain routing, taxonomy translation and arbitrary page-builder data require additional compatibility work. Do not activate alongside another multilingual routing plugin without testing.

== Installation ==
1. Upload qentrah-languages.zip using Plugins > Add New > Upload Plugin.
2. Activate and open Settings > Qentrah Languages.
3. Add your languages and set the default.
4. Edit a page, save changes, then use Languages to create or open translations.
5. Publish translations after reviewing their copy. Insert the Language switcher block or shortcode in your theme.

== Frequently Asked Questions ==
= Is this automatic translation? =
No. A new translation is an editable draft. A human must translate and review it.

= Does it require the Dentora theme? =
No. It works independently. Dentora adds an optional adapter for its starter layouts.

= What happens when I remove it? =
Your posts, settings and relationship metadata remain. The editor controls, switcher and hreflang enhancements stop.

= Can I use my own URLs? =
Yes. It uses each post's normal WordPress permalink and supports plain or pretty permalinks. Language-specific domains and automatic prefix rewriting are not included.

== Changelog ==
= 0.1.0 =
Initial preview: native editing workflow, language relationships, switcher, metadata and export.

== Changelog ==
= 0.2.0 =
Dedicated Languages menu, toolbar shortcut, searchable page/post translation manager, create-and-edit drafts, optional locale, and illustrated four-language manuals.
