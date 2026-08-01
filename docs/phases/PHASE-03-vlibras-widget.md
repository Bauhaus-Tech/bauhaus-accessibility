# PHASE-03 — VLibras Widget (Frontend)

## Goal
The VLibras Brazilian Sign Language virtual interpreter appears on the front-end
when enabled. The main `vlibras-plugin.js` is bundled locally; chunks and Unity
assets continue loading from `vlibras.gov.br` at runtime.

## In scope

- R1: Download `vlibras-plugin.js` from `https://vlibras.gov.br/app/vlibras-plugin.js`
  and place it in `assets/js/vlibras-plugin.js`
- R2: `VlibrasWidget` class that enqueues the local script, injects the VLibras
  container markup into `wp_footer`, and initializes with
  `new window.VLibras.Widget('https://vlibras.gov.br/app')`
- R3: The widget respects the admin's `widget_position` setting (left/right)
  and CSS positions the VLibras button stacked with Sienna
- R4: The widget only loads when `enable_vlibras` is true
- R5: The sign_language dropdown (initially only `libras`) is wired to the
  VLibras rootPath parameter — for now it always passes the default gov.br URL

## Out of scope

- Multiple sign languages (only Libras exists in v1)
- Making VLibras chunks/assets fully local (hybrid approach per PLAN.md)
- Settings page fields (Phase 04)

## Acceptance criteria

| # | Criterion | Verified by |
|---|-----------|-------------|
| A1 | VLibras button appears on front-end when enabled | Manual: curl page, verify vw-container markup |
| A2 | VLibras does not load when disabled | Manual: disable, verify no VLibras markup/script |
| A3 | VLibras button stacks with Sienna on chosen side | Manual: both enabled, verify both buttons present |
| A4 | vlibras-plugin.js loaded from local plugin directory | Manual: curl page, verify src attribute |
| A5 | VLibras init call passes correct rootPath | Manual: verify `new window.VLibras.Widget(...)` in source |

## Tooling exemption

- `assets/js/vlibras-plugin.js` — third-party bundle, no behavior of our own
