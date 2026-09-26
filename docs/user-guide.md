# Languages and editing

## Add a language

Open Settings → Qentrah Languages. Enter a code (for example `fr`), display name (`Français`), locale (`fr_FR`) and direction. Save. Use the same code to update its label/direction. English and Arabic are defaults, not a hard limit. Set the site's default language separately.

## Translate a page

Open Pages → Edit and save current changes. In the editor's **Languages** panel, choose **Create draft** next to the destination language. This copies editable content into a separate draft. It is not automatic translation. Translate the title, text, image descriptions, buttons, excerpt and slug, then preview and publish.

Use **Edit translation** to switch back to the matching page in another language. The control is disabled while your current page has unsaved changes. Published translations appear in the visitor switcher; missing/draft translations do not. A review notice appears if the source content changes after the copy was created.

Already have another-language content? Enter its page ID in **Link an existing page ID**, then choose the language. You need permission to edit both pages. The plugin refuses to replace an existing language relationship or steal a page from another multi-page translation group. The classic language metabox also exposes links and a page-language selector.

## Show languages on the site

Insert the **Language switcher** block or `[qentrah_languages]` shortcode. Theme developers can call `ql_switcher()`. The Dentora Production header integrates this automatically. It uses actual published page permalinks; no forced redirects based on guessed location or browser language.

## Data and backups

Content remains normal WordPress posts/pages. Export content through Tools → Export, and export language settings/relationships through Settings → Qentrah Languages. Relationship JSON contains local post IDs and URLs; it is an audit/migration aid, not an automatic cross-site import. Back up the database for a full restore.

Deactivation/uninstall retains posts, options and metadata. Plugin controls and language SEO enhancements stop while it is inactive. Removing a plugin is not the same as deleting translated content.

## Current boundaries

Additional languages need human-authored text and navigation. No machine translation, per-domain routing, taxonomy synchronization or WooCommerce synchronization is included. A copied page is a draft until reviewed. Different themes may need placement of the switcher and language-specific menus. Existing theme strings follow WordPress's own translations, not content-copy translation.
