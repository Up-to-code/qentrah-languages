# Multilingual WordPress market research

Research date: 2026-09-26. This is a qualitative desk study, not a representative survey or benchmark of competing products. User reports identify test cases; they do not establish prevalence or prove that a plugin caused a site's problem. Prices and feature boundaries can change.

## Brand context

[Qentrah](https://www.qentrah.com/) describes its work as websites, applications, commerce systems, integrations and reliable digital infrastructure. Its presentation is restrained, technical and monochrome. The proposed product is **Qentrah Languages**: a standalone, theme-independent plugin. Dentora Lab is its first integration, not its identity. Use black/white branding, clear controls and honest compatibility statements.

## Competitors and trade-offs

| Product | Established strength | Friction or trade-off supported by research | Qentrah response to test |
|---|---|---|---|
| WPML | Extensive translation workflow and integration ecosystem | Vendor maintains performance triage and compatibility errata. Admin, frontend, external services and caching require separate diagnosis. | Keep translation editing in the native editor; measure database/query overhead; publish tested compatibility scope. |
| Polylang | WordPress-native language relationships and editorial control | Site Editor patterns/navigation are documented as Pro features; downgrade can affect Pro-specific URL features. | Include editor language navigation in the base plugin; preserve normal posts and URLs without a subscription. |
| TranslatePress | Visual frontend translation and optional machine translation | Vendor documents additional runtime output processing; a support report highlights dynamic-string detection and translation-memory costs. SEO/extra-language features have add-on boundaries. | Avoid translating/render-rewriting the whole output; use normal posts, explicit relationships and per-language URLs. |
| Weglot | Managed translation workflow across platforms | Export documentation describes plan requirements; quota and subscription management are operational considerations. | Local data and portable JSON relationship export, no required account, no frontend external API call. |

Sources:
- [WPML performance triage](https://wpml.org/troubleshooting/performance/)
- [WPML known issues](https://wpml.org/errata/): includes both resolved and open integration issues; do not present resolved issues as current defects.
- [Polylang Site Editor guide](https://polylang.pro/documentation/support/guides/site-editor/)
- [Polylang multilingual navigation](https://polylang.pro/documentation/support/guides/multilingual-navigation-site-editor/)
- [Polylang downgrade support answer](https://wordpress.org/support/topic/from-pro-to-free-version-what-about-my-translated-content/)
- [TranslatePress performance support discussion](https://wordpress.org/support/topic/performance-issues-caused-by-translatepress/)
- [TranslatePress automatic translation](https://translatepress.com/docs/automatic-translation/)
- [TranslatePress add-ons](https://translatepress.com/docs/addons/)
- [Weglot export documentation](https://support.weglot.com/article/206-can-i-export-my-translations)
- [Weglot import/export scope](https://support.weglot.com/article/432-what-can-we-export-import)
- [Weglot quota handling](https://support.weglot.com/article/298-what-is-the-auto-upgrade)

## Issue taxonomy and acceptance tests

1. **The wrong language appears after navigation.** Resolve switchers to the matching published translation, omit missing translations, and never silently send an editor to an unrelated homepage. Test query-string and pretty permalinks, draft translations and a third language.
2. **Custom block text is missing or lost.** Preserve full serialized blocks when creating a draft translation, never overwrite the original, and test custom block attributes. A support thread about AutoPoly custom-block support is an add-on issue, not proof of a defect in Polylang core: [thread](https://wordpress.org/support/topic/posts-are-translated-but-translations-is-lost/).
3. **Changes appear to overwrite translations.** Separate posts and revisions; explicit create/link actions; no implicit synchronization of authored content. Detect source changes and show a review-needed state.
4. **Unclear content ownership.** No external service required; export relationships; deactivation/uninstall retain authored posts and metadata. Explain that plugin UI/SEO enhancements stop when deactivated.
5. **SEO mistakes.** Reciprocal hreflang only for published siblings; correct HTML language/direction, native canonical URLs and core sitemap compatibility. Do not label untranslated draft copies as finished translations.
6. **Performance surprises.** No frontend API calls or whole-document translation parsing. Benchmark on the local WordPress fixture and repeat on client hosting; never market local Lighthouse numbers as field Core Web Vitals.
7. **Incompatible editors/themes.** Test native blocks, the Dentora custom blocks, a default WordPress theme, classic metaboxes and REST permissions. Do not claim universal page-builder/WooCommerce compatibility before testing.

## Proposed positioning

“Own your languages. Edit in WordPress.” Target independent studios and agencies handing multilingual marketing sites to clients, especially English/Arabic sites. Differentiate with predictable editing and handoff, not an unsupported claim to outperform established plugins. The strongest first release is a small local-first editorial tool, not a replacement for every WPML/Weglot feature.

## Release scope

- **Base:** arbitrary configured languages and direction, native editor language switcher, create/link translations, visible status, frontend shortcode/block switcher, hreflang, relationships export, role/capability checks, nondestructive deactivation.
- **Later, only with separate tests:** automated translation providers with consent/budget controls, WooCommerce/catalog translation, taxonomy synchronization, multisite/domain routing, third-party builder-specific adapters.
- Machine translation is not equivalent to editing the other-language page. Base release creates editable drafts; it does not pretend to translate copy automatically.

## Distribution constraints

Build and test on every GitHub push. Attach installable ZIPs to tagged GitHub releases. WordPress.org requires a complete plugin and manual initial review; SVN credentials become usable after acceptance. Only versioned releases should deploy to SVN. No account credentials belong in source or ZIPs.

- [Submission process](https://developer.wordpress.org/plugins/wordpress-org/planning-submitting-and-maintaining-plugins/)
- [Plugin guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
- [SVN release workflow](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/)

## Commercial readiness is separate from implementation

Validate demand with real editors, support costs and external hosting. Check the rights to all template photographs, reference designs and fonts before selling Dentora; the source repository's inclusion of an asset is not evidence of a redistribution license. Plugin code can be licensed GPL-2.0-or-later; do not grant rights you do not hold to unrelated theme assets.
