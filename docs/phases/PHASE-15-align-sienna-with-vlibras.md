# PHASE-15 — Align Sienna control with VLibras

## Goal

When both accessibility widgets use the same side of a page, Sienna's compact
control stays visually aligned with VLibras even though the bundled Sienna
runtime writes a 10px horizontal offset as an inline style.

## In scope

1. R1: In left-position mode, the plugin stylesheet overrides Sienna's inline
   offset with `left: 20px !important`.
2. R2: In right-position mode, the plugin stylesheet overrides Sienna's inline
   offset with `right: 20px !important`.
3. R3: A permanent regression test verifies both side-specific overrides.
4. R4: The README and manual test script describe the aligned controls.

## Out of scope

- Changing Sienna's bundled runtime or its default positioning logic.
- Changing VLibras's positioning, size, or external runtime.
- Changing vertical placement of either control.

## Acceptance criteria

| # | Criterion | Verified by |
|---|-----------|-------------|
| A1 | Left-position mode contains `left: 20px !important` for Sienna's control. | `SiennaDistributionTest::test_plugin_styles_override_inline_sienna_offsets_to_align_with_vlibras` |
| A2 | Right-position mode contains `right: 20px !important` for Sienna's control. | `SiennaDistributionTest::test_plugin_styles_override_inline_sienna_offsets_to_align_with_vlibras` |
| A3 | A public page with both widgets on the same configured side shows aligned controls. | `team/WORKITEMS/W-015/local-position-proof.cjs` |

## Risks / open questions

- The stylesheet must use `!important` because Sienna writes its 10px offset
  directly on the button element; a normal stylesheet declaration cannot
  override an inline style.

## Phase report

- RED: `vendor/bin/phpunit --filter
  test_plugin_styles_override_inline_sienna_offsets_to_align_with_vlibras`
  failed because both 20px declarations lacked `!important`.
- GREEN: the same command passed with 1 test and 5 assertions after adding
  the two override declarations.
- Browser verification: `centrodememoria.local` exposed `WordPress 7.1` and,
  with Sienna's inline offset set to 10px, computed `left: 20px` in left mode
  and computed `right: 20px` in right mode.
