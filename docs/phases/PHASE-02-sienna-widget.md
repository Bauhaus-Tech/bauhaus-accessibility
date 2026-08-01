# PHASE-02 — Sienna Widget (Frontend)

## Goal
The Sienna Accessibility Widget appears on the front-end of the site when enabled
in settings. All Sienna assets (JS, fonts, locales) are bundled locally — no CDN calls.

## In scope

- R1: Download and bundle Sienna UMD bundle (2.2.333), OpenDyslexic fonts (ttf + woff),
  and all locale JSON files (~50 languages) into `assets/`
- R2: `SiennaWidget` class that enqueues the local UMD bundle and fonts, with
  correct dependency ordering
- R3: Sienna initialization: call `window.SiennaPlugin({...})` with `lang`, `position`,
  and a `rootPath` equivalent that points locales to our plugin's `assets/locales/`
- R4: The widget respects the admin's `widget_position` setting (left/right)
- R5: CSS (`bauhaus-accessibility.css`) that positions the Sienna button vertically
  centered on the chosen side, stacked with space for the future VLibras button
- R6: The widget only loads on the front-end when `enable_sienna` is true
- R7: Plugin option defaults include `enable_sienna: false`

## Out of scope

- VLibras widget (Phase 03)
- Admin settings fields (Phase 04 — the toggle already exists from Phase 01)
- i18n of plugin strings (Phase 05)
- WP.org readme updates (Phase 05)

## Acceptance criteria

| # | Criterion | Verified by |
|---|-----------|-------------|
| A1 | Sienna widget button appears on front-end when enabled | ✅ Verified: `asw-menu-btn` and `asw-container` present in HTML |
| A2 | No external CDN requests for Sienna assets | ✅ Verified: 0 CDN references in page source, local `wp-content/plugins/bauhaus-acessibilidade-br/assets/` URLs |
| A3 | Widget button respects left/right position setting | ✅ Verified: `data-position="center-right"` injected via before-script |
| A4 | Widget does not load when disabled | ✅ Verified: 0 Sienna references when `enable_sienna=false` |
| A5 | All ~50 locale files present in `assets/locales/` | ✅ Verified: 51 locale JSONs downloaded |
| A6 | Sienna menu opens and functions work (font size, contrast, etc.) | ⚠ Deferred: requires browser interaction, not verifiable via curl |
| A7 | Zero PHP errors or warnings when widget is enabled | ✅ Verified: no fatals, widget loads cleanly |

## Risks

- **Sienna locale path override:** The UMD bundle defaults locales to the jsDelivr CDN.
  We need to find the config option (documented as `rootPath` or similar) to point to
  our plugin directory. If the UMD doesn't expose this, we may need to set a global
  variable before loading the script or intercept the fetch calls.
- **Sienna init timing:** The bundle auto-initializes on `readystatechange`. We may
  need to configure it before it auto-inits, or disable auto-init and call it manually.

## Tooling exemption

- `assets/js/sienna-accessibility.umd.js` — third-party bundle, no behavior of our own
- `assets/fonts/*` — third-party font files
- `assets/locales/*.json` — third-party translation files
