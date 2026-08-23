# PHASE-13 — Remove legacy Sienna locale files

## Goal

Remove the obsolete standalone Sienna locale JSON files so the installable
plugin contains only the locale data bundled in the current local Sienna UMD.

## In scope

1. Remove the unused `assets/locales/` directory.
2. Prove the installable ZIP excludes that legacy directory.
3. Preserve the current local Sienna toolbar and its bundled locale behavior.

## Out of scope

- Changing the locale modules compiled into the Sienna UMD.
- Changing WordPress.org language-pack handling.
- Changing VLibras or Sienna toolbar features.

## Acceptance criteria

| # | Criterion | Verified by |
|---|---|---|
| A1 | No standalone `assets/locales/` directory remains. | Packaging test and repository scan |
| A2 | The installable ZIP has no `assets/locales/` entries. | ZIP integration test |
| A3 | The existing local Sienna runtime checks remain green. | Full PHP test suite |

## Risks / open questions

- The Sienna UMD deliberately contains its own locale modules; those are runtime
  code, not the removed legacy files.
