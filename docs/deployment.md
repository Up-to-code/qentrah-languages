# Build and distribution

## GitHub

Every push/PR runs PHP syntax checks, a real WordPress integration fixture and ZIP packaging. Download the CI artifact for a commit. For a release, update the plugin header, `QL_VERSION`, `readme.txt` Stable tag, docs and product descriptor together, then push a matching `vX.Y.Z` tag. The release workflow attaches the ZIP as a preview release.

## WordPress.org — first publication

1. Confirm the owning WordPress.org username and company contact. Replace the provisional `Contributors` entry with the actual account; a GitHub username does not establish a WordPress.org account.
2. Complete Plugin Check, security review, documentation/asset verification and preview-user testing. Resolve all mandatory checks; validate claims and compatibility boundaries.
3. Submit the complete installable ZIP through https://wordpress.org/plugins/developers/add/ using the owner account. Initial review is manual and approval is not guaranteed.
4. After approval, record the assigned slug and grant the repository's publishing identity access to the approved SVN repository.
5. Add GitHub environment **wordpress-org**. Add secrets `WP_ORG_USERNAME` and `WP_ORG_SVN_PASSWORD`, repository variables `WP_ORG_SLUG` and `WORDPRESS_ORG_APPROVED=true`. Use the account's SVN-specific password; never put credentials in source or chat.
6. Push a reviewed stable version tag. The deployment job runs only after the GitHub release job and only when the approval variable is true. The workflow checks that the tag is a stable numeric version. It uses the WordPress deployment action to upload release files, not ZIPs, to SVN.

The approval variable must remain unset/false until the plugin is approved and all gates are resolved. Ordinary development pushes build artifacts but do not publish to the WordPress directory. This follows WordPress's distinction between a development repository and SVN's release role.

Sources: [submission](https://developer.wordpress.org/plugins/wordpress-org/planning-submitting-and-maintaining-plugins/), [guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/), [SVN](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/).
