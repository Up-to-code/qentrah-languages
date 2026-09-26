# Plugin architecture

`_ql_language` stores the configured language code; `_ql_group` is a UUID shared by translated posts of the same post type. `_ql_source_id` and `_ql_source_hash` support source-change notices. Settings live in `ql_languages` and `ql_default_language`. No proprietary hosted translation database is required.

Public helpers: `ql_languages()`, `ql_default_language()`, `ql_post_language($id)`, `ql_current_language()`, `ql_translations($id,$published)`, `ql_switcher($id)`. Internal/admin helpers `ql_create_translation` enforce edit/create capabilities; callers of `ql_link_translation` must enforce authorization before use. REST endpoints enforce edit capability and WordPress REST authentication/nonces.

Filters/actions:
- `ql_translation_content($content,$source,$language)` adapts copied blocks without changing source content.
- `ql_translation_created($id,$source,$language)` allows an explicit adapter to copy safe platform metadata. Register with three accepted arguments.

The Dentora adapter is in the theme, not the plugin. The core plugin has no Dentora class/path requirement. Normal WordPress URLs are used, so language groups do not require rewrite rules. `ql_lang` can explicitly filter archive queries; automatic multilingual taxonomy/archive routing is outside this preview.

Security: validate configured codes, escape output/URLs, check nonces and capabilities, copy to draft, avoid overwriting language groups, keep public switchers to published content, and retain authored data on uninstall. Tests cover permission denial and relationship collisions. WordPress Plugin Check is an aid, not a substitute for manual directory review or a security audit.

Future adapters must not copy arbitrary hidden post metadata by default. Add explicit schema-aware copies with permission tests. Keep translations and revisions independent. Do not add external translation calls without an explicit provider configuration, consent, failure handling and cost controls.
