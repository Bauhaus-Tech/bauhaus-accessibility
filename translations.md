# WordPress.org translation workflow

The plugin interface is translated through WordPress.org language packs. Do
not add `.pot`, `.po`, `.mo`, or `.l10n.php` files to the plugin package, and
do not commit a translation file to the WordPress.org plugin repository.

`docs/translations/bauhaus-accessibility-pt_BR.po` is the Brazilian Portuguese
source translation maintained in this Git repository. The directory is
excluded from the installable ZIP by `.distignore`.

## Before contributing

1. Confirm that the approved WordPress.org plugin slug is
   `bauhaus-accessibility`. The slug, the plugin header's text domain, every
   gettext call, and the PO filename must use the same value. If WordPress.org
   assigns a different slug, update all four in Git before making translation
   suggestions.
2. Update the PO source whenever an English WordPress interface string changes.
   Preserve each `msgid` exactly; WordPress.org matches translations to those
   source strings.
3. Commit the source PO only to this Git repository. It is a contribution
   reference, not a runtime file.

## Contribute Brazilian Portuguese

1. Publish the plugin version through the normal WordPress.org release process
   so WordPress.org can extract the current English strings.
2. Open the plugin's Brazilian Portuguese project on
   [translate.wordpress.org](https://translate.wordpress.org/), select the
   current stable project, and submit each translation from the source PO as a
   suggestion. A Brazilian Portuguese translation editor can also use the
   platform's import feature when they have permission to do so.
3. Ask a Brazilian Portuguese translation editor to review and approve the
   suggestions. Only approved translations are used to generate the language
   pack.
4. After the language pack is generated, install or update translations from
   the WordPress administration updates screen and verify the plugin interface
   with the site language set to Portuguese (Brazil).

## Release boundary

Only plugin code and runtime assets belong in the WordPress.org plugin
repository. Never copy `docs/translations/bauhaus-accessibility-pt_BR.po` or
any compiled translation artifact into that repository. WordPress downloads
the approved language pack separately for sites that use `pt_BR`.
