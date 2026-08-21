# PHASE-09 — WordPress.org language packs

## Goal

The plugin relies on WordPress.org language packs for its WordPress interface
translations while retaining the source material needed to contribute the
Brazilian Portuguese translation through the official translation platform.

## In scope

1. R1: Remove the manual WordPress text-domain loader and its `init` hook while
   retaining the `bauhaus-accessibility` text domain for string extraction.
2. R2: Remove bundled WordPress translation artifacts from the plugin package;
   keep the Brazilian Portuguese source translation only in
   `docs/translations/`.
3. R3: Document the workflow for contributing the Brazilian Portuguese
   translation through WordPress.org without committing translation files to
   the WordPress.org plugin repository.

## Out of scope

- Changing gettext calls or the English source strings used by the WordPress
  interface.
- Changing Sienna's bundled `assets/locales/` files, which are its independent
  browser-side locale data rather than WordPress plugin translations.
- Translating additional locales or translating the third-party Sienna widget.

## Acceptance criteria

| # | Criterion | Verified by |
|---|---|---|
| A1 | Bootstrap registers no manual text-domain loader and still registers the plugin boot callback. | `tests/Core/PluginBootstrapTest.php` |
| A2 | The distributable plugin has no `languages/` directory, and the Brazilian Portuguese source translation is stored only in `docs/translations/`. | `tests/Core/TranslationPackagingTest.php` |
| A3 | A maintainer can follow `translations.md` to submit the documented source translation through WordPress.org without using the plugin repository. | Documentation review |

## Risks / open questions

- The eventual WordPress.org plugin slug must remain
  `bauhaus-accessibility`; the text domain must match it for language packs to
  load. The current plugin header and gettext calls already use that value.
