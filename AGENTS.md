# Qentrah product rules

Read `.qentrah-product.json` and the linked original family registry before updates.


- This is a standalone public plugin repository. The linked family registry belongs to the original design repository; do not publish private theme source here.
- Each implementation lives in its own repository. Preserve the legacy WordPress repository; develop the new production implementation separately. The standalone language plugin is not owned by a theme.
- Whenever creating, cloning or deriving a new product, register its real repository URL, platform, status, version, page coverage, documentation and artifact URL in the family registry and README. Add a backlink and `.qentrah-product.json` to the child. Do not list a planned repository as shipped.
- Track the source design commit independently of downstream implementation commits. Never infer that a child is synchronized merely because its version is newer.
- When asked to update all references, read the registry, inspect each child and its local rules, compare source changes, apply platform-appropriate changes, and run that child's tests. Preserve stable field/layout IDs and client-authored text, translations, media and global settings. No blind replacement of production content.
- Keep a migration/backup path, bump product versions, update changelogs/docs/page inventories, build installable ZIPs, and attach checksummed artifacts to versioned releases. Verify links and CI outcomes before calling a release complete.
- Update upstream and child READMEs together. Headless/CMS adaptations must follow the same rule and expose their appropriate deploy/install artifact.
- Multilingual relationships and language management belong in Qentrah Languages or another explicit plugin integration. Themes must remain functional in one language without it. Editors must be able to switch to the matching language, preserve unsaved work, and add languages without source-code edits.
- Global colors/fonts must be editable in WordPress, reflected in the editor and frontend, sanitized and preserved through updates.
- Treat marketplace claims as testable claims. Distinguish lab SEO/performance checks from field metrics, tested compatibility from aspirations, and preview releases from approved WordPress.org releases. Verify redistribution rights before marking third-party assets commercially cleared.
- CI builds on pushes. Public WordPress.org deployment is a versioned-release action gated by initial approval and configured secrets, not a mirror of every development commit.

- Keep the WordPress Languages manager and its illustrated manuals in sync. Regenerate EN/AR/ES/FR offline HTML using `node tools/manual.mjs`; release the manual ZIP beside the plugin. Screenshots must come from the real interface, exclude private data, and identify the installed admin language accurately.
