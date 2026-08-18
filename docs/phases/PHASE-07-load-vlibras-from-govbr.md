# PHASE-07 — Load VLibras from gov.br

## Goal

When enabled, the VLibras widget continues to offer the configured left or right
placement while WordPress loads the official widget script directly from
`vlibras.gov.br`, rather than distributing a local copy.

## In scope

1. R1: Enqueue `https://vlibras.gov.br/app/vlibras-plugin.js` only when
   `enable_vlibras` is enabled.
2. R2: Preserve the existing VLibras `rootPath` and left/right position
   configuration when initializing the remote script.
3. R3: Remove the local VLibras bundle and document that its script and runtime
   assets load directly from `vlibras.gov.br`.

## Out of scope

- Changing the VLibras widget's design, features, or government-hosted script.
- Changing Sienna behavior or its local asset distribution.
- Adding a fallback when the government service is unavailable.

## Acceptance criteria

| # | Criterion | Verified by |
|---|---|---|
| A1 | An enabled widget enqueues the exact gov.br widget-script URL. | `tests/Frontend/VlibrasWidgetTest.php` |
| A2 | The initializer retains the gov.br root path and configured left/right position. | `tests/Frontend/VlibrasWidgetTest.php` |
| A3 | The plugin no longer contains a local VLibras JavaScript bundle, and installation documentation discloses the remote load. | `VlibrasWidgetTest::test_plugin_does_not_include_a_local_vlibras_script()`; documentation review |

## Risks / open questions

- The government-hosted service must be reachable for the widget to load. This
  external dependency is disclosed in the installation documentation.
- Browser verification found that the government widget's supplied access-button
  markup does not receive keyboard focus or open with Enter. Pointer activation
  works. Improving that third-party interaction is outside this phase and needs
  an owner decision.
