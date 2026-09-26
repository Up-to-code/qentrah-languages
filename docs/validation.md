# Validation and remaining release gates

Local environment: WordPress 7.1.2 / PHP 8.5.11, Dentora Production adapter active. Integration tests exercise core-block content, third-language draft creation, published-only relationships, duplicate prevention, invalid languages, source-change warnings and subscriber/REST permission denial. Fixtures are removed and settings restored.

WordPress Plugin Check static checks were run. The initial suppress-filters error and input-normalization warnings were fixed. Remaining query warnings concern postmeta relationship lookup/export; they are not evidence of measured slowdown, but large-site scale needs profiling and likely an indexed taxonomy/table strategy before claiming large-catalog support. Manual directory review and a broader security/compatibility review are still required.

The first product is intentionally a preview. No claim is made to automatic translation, WooCommerce integration, all page builders, multisite/domain routing, taxonomy synchronization, large-catalog performance, or WordPress.org approval.

Before public release: confirm the actual WordPress.org owner and slug, localize all admin labels, complete the directory review, test on supported hosting/PHP versions and default themes, verify backup/restore/export behavior, and conduct editor usability testing. GitHub CI verifies the independent plugin on a clean WordPress installation.
