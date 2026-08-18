# PLAN — Bauhaus Accessibility

## Goal

A WordPress plugin, submitted to the wordpress.org plugin repository, that adds two
accessibility widgets to the front-end of any WordPress site:

1. **Sienna Accessibility Widget** — a full-featured accessibility toolbar (contrast,
   font size, screen reader, profiles, etc.). All Sienna assets (JS, fonts, locales) are
   bundled locally — no external CDN calls.
2. **VLibras Widget** — the Brazilian government's Libras (Brazilian Sign Language)
   virtual interpreter. Its official script and runtime assets load directly from
   `vlibras.gov.br` when the widget is enabled (see §External Dependencies).

Both widgets appear as **two vertically-stacked buttons, vertically centered**. The
administrator can choose whether the buttons appear on the **far right** or **far left**
of the page. A settings page (under Settings → Accessibility) lets the administrator
enable/disable each widget independently, choose the side, and select the sign language
for VLibras (initially only Libras).

---

## Architecture

```
bauhaus-accessibility/
├── bauhaus-accessibility.php    # Plugin bootstrap
├── readme.txt                       # wordpress.org readme
├── src/
│   ├── Admin/
│   │   └── SettingsPage.php         # Admin settings page + settings registration
│   ├── Frontend/
│   │   ├── SiennaWidget.php         # Enqueues + outputs Sienna
│   │   └── VlibrasWidget.php        # Enqueues + outputs VLibras
│   ├── Core/
│   │   └── Plugin.php               # Main plugin class, hooks everything together
│   └── Assets/
│       └── AssetManifest.php        # Registers all scripts/styles with WordPress
├── assets/
│   ├── js/
│   │   └── sienna-accessibility.umd.js   # Sienna 2.2.x bundle
│   ├── fonts/
│   │   ├── OpenDyslexic3-Regular.ttf
│   │   └── OpenDyslexic3-Regular.woff
│   ├── locales/
│   │   ├── en.json                     # + ~50 locale files for Sienna
│   │   ├── pt.json
│   │   └── ... (all Sienna locale files)
│   └── css/
│       └── bauhaus-accessibility.css    # Widget button positioning
├── languages/
│   └── bauhaus-accessibility.pot    # Translation template
└── uninstall.php                        # Cleanup on uninstall
```

### Design decisions

| Decision | Rationale |
|----------|-----------|
| **Sienna assets fully local** | WordPress.org guidelines prefer self-contained plugins. The Sienna UMD bundle references fonts and locales at predictable paths; these are downloaded and placed under `assets/`. |
| **VLibras script and assets hosted by gov.br** | The official service remains the authoritative source for its script and runtime assets. Distributing a local copy raised review and licensing concerns, while remote loading keeps the service version under its maintainer's control. The readme discloses this external dependency. |
| **Two separate widget classes** | SRP: each widget has its own enqueue logic, markup injection, and toggle. The admin page is a third concern. |
| **WordPress Settings API** | Standard, secure, no custom table needed. Two checkboxes + one dropdown → single option array. |
| **Side-selectable vertical centering via CSS** | The admin chooses left or right. CSS custom properties set the side; a single class toggles between `left: 0` / `right: 0`. The old plugin's CSS approach (`position: fixed; top: calc(50% ...)`) works as a starting point; we refine it for two stacked buttons. |
| **No build step** | WordPress.org plugins are distributed as plain PHP/JS/CSS. We ship the unminified Sienna UMD source; VLibras loads from its official service. No composer/npm build required at install time. |
| **Settings under Settings menu** | WordPress.org convention for utility/configuration plugins. Not a top-level menu. |
| **Clean implementation** | The old `esun-acessibilidade-br` plugin is procedural and targets Sienna 1.x. The new Sienna 2.x has a completely different API and positioning system. Starting fresh avoids carrying dead abstraction. |

---

## External Dependencies & Licensing

| Library | Version | License | Bundled? | Notes |
|---------|---------|---------|----------|-------|
| Sienna Accessibility | 2.2.x | MIT | **Yes** (JS + fonts + ~50 locale JSONs) | MIT is GPLv2-compatible. Bundled under `assets/js/`, `assets/fonts/`, `assets/locales/`. |
| VLibras Widget | latest from vlibras.gov.br | Government-hosted service | **No** | Widget script and runtime assets load from `https://vlibras.gov.br` when enabled. |
| OpenDyslexic font | 3 | SIL-OFL / MIT | **Yes** (bundled with Sienna) | OpenDyslexic is SIL Open Font License — compatible. |

---

## Phases

### Phase 01 — Scaffold & Bootstrap

**Scope:** Plugin skeleton that activates without errors, registers its presence, and
passes basic WordPress.org checks.

- Plugin header, GPLv2+ license block, ABSPATH guard
- `readme.txt` with valid headers
- Main `Plugin.php` class: hooks init, registers scripts, no-op for now
- Empty admin page (just a title)
- `uninstall.php`
- PHPCS/phpstan config (tooling only — exempt from TDD per AGENTS.md §2.1)

### Phase 02 — Sienna Widget (Frontend)

**Scope:** Sienna Accessibility Widget appears on the front-end when enabled.

- `SiennaWidget.php`: enqueues the local UMD bundle + fonts + CSS, injects the
  Sienna initialization call
- `bauhaus-accessibility.css`: positions the Sienna button on the far right,
  vertically centered
- Admin toggle wired: enable/disable the Sienna widget
- All Sienna assets bundled in `assets/`

**Key technical detail:** Sienna 2.x reads its `position` option from a `data-position`
attribute on the script tag or `document.currentScript`. Since we control enqueueing,
we pass `position: "center-right"` (or the equivalent position config) through the
Sienna API (`window.SiennaPlugin({options})`). The old plugin overrides positioning
via CSS; the new Sienna accepts `position` as an init option (values:
`center-right`, `bottom-left`, etc.). We use `center-right` and also ship our own CSS
to ensure the button stacks correctly with VLibras.

**Sienna localisation:** Sienna loads locale JSONs from
`{root}/locales/{lang}.json`. Its default root is the jsDelivr CDN. We will set
the root to `plugin_dir_url(__FILE__) . 'assets/'` by modifying the init call
or by patching the public path. If the UMD bundle makes this configurable, we use
the config; otherwise, we note the constraint and host locales at the expected
relative path.

### Phase 03 — VLibras Widget (Frontend)

> Superseded in part by Phase 07: VLibras now loads its official widget script
> directly from `vlibras.gov.br`; the plugin no longer bundles that script.

**Scope:** VLibras virtual interpreter appears on the front-end when enabled.

- `VlibrasWidget.php`: enqueues the official `vlibras-plugin.js` URL, injects the VLibras
  container markup into `wp_footer`, and calls `new window.VLibras.Widget(...)`
- The widget button is positioned via CSS to stack below/above the Sienna button
- Admin toggle wired
- Sign language dropdown on admin page (initially one option: "Libras")
- The dropdown value is used only to configure which VLibras avatar/sign language;
  for v1 with only Libras, it sets the root path

**Markup injected at `wp_footer`:**
```html
<div vw class="enabled">
  <div vw-access-button class="active"></div>
  <div vw-plugin-wrapper>
    <div class="vw-plugin-top-wrapper"></div>
  </div>
</div>
```

### Phase 04 — Admin Settings Page

**Scope:** A polished admin settings page under **Settings → Accessibility**.

- WordPress Settings API with proper nonce, sanitization, and validation
- Checkbox: "Enable VLibras Sign Language Interpreter"
- Checkbox: "Enable Accessibility Widget" (Sienna)
- Radio or dropdown: "Widget position" — `right` (default) or `left`
- Dropdown: "Sign Language" — populated from a filterable list; default: `[ "libras" => "Libras (Brazilian Sign Language)" ]`
- Save, success/error notices
- `settings_fields()`, `do_settings_sections()`, `submit_button()`
- Customizer integration deferred to a future phase

### Phase 05 — WP.org Readiness

**Scope:** Plugin passes wordpress.org submission review.

- `readme.txt` with complete Description, Installation, FAQ, Changelog, Upgrade Notice
- All strings internationalized (`__()`, `_e()`, `esc_html__()`, etc.)
- `.pot` file in `languages/`
- No vendor attribution anywhere (AGENTS.md §2.5)
- All third-party assets declared and licensed in readme
- Plugin URI, Author URI, License fields correct
- WordPress Plugin Check (PCP) check clean
- WordPress CS / PHPCS clean
- Tested on WordPress latest

### Phase 06 — Adversarial Review & Polish

**Scope:** Final review pass over the full codebase.

- Run `docs/agents/implementation-reviewer.md`
- Apply or escalate every finding
- Consolidate CSS, remove dead code, ensure consistent naming
- Verify both widgets work simultaneously without conflict
- Verify widget buttons stack correctly on mobile

### Phase 07 — Load VLibras from gov.br

**Scope:** Replace the bundled VLibras script with the official government-hosted
widget script, preserve the configured side, remove the local bundle, and document
the external dependency.

### Phase 08 — Clear the Sienna lint warning

**Scope:** Document the intentional local Sienna bundle read narrowly enough for
the project-wide coding-standard check to complete without warnings.

---

## Resolved Decisions

1. **VLibras: government-hosted approach.** Load the official `vlibras-plugin.js` script
   and its runtime assets directly from `vlibras.gov.br` when enabled. The plugin does not
   distribute VLibras JavaScript; the configured left/right position remains in its
   initializer.

2. **Settings menu placement.** Under Settings → Accessibility, following wordpress.org
   convention for utility plugins.

3. **Clean implementation.** The old `esun-acessibilidade-br` plugin targets Sienna 1.x
   with a completely different API. Starting fresh is the right call.

4. **Widget side selector.** Admin can choose left or right positioning. Applied via
   a CSS class and passed to each widget's config where the widget supports it.

## Open Risks

1. **Sienna locale path patching.** The Sienna UMD expects locales at
   `{base}/locales/{lang}.json`. Its public path defaults to the jsDelivr CDN. We need to
   confirm we can override this at init time (the `rootPath` or equivalent config). If the
   UMD doesn't expose this, we may need to set a global before loading the bundle. Needs
   verification during Phase 02.

2. **Sienna version pinning.** Pin to 2.2.333 (latest at time of writing). The upgrade
   procedure is: download the new UMD bundle, download any new locale files, verify the
   public API hasn't changed, update the version constant.

3. **Button stacking.** Both widgets use `position: fixed`. We need to vertically offset
   one relative to the other (e.g., Sienna at center, VLibras 60px above/below) on both
   left and right sides. This is straightforward CSS but must be tested on mobile.

4. **WordPress.org review: remote asset loading.** The VLibras widget script and its
   runtime assets load from `vlibras.gov.br` only when the administrator enables VLibras.
   This external dependency is disclosed in the readme.

---

## Acceptance Criteria (Cross-Phase)

1. Plugin activates without errors on WordPress 7.0+ / PHP 8.2+
2. Admin settings page lets me enable/disable each widget independently
3. VLibras button appears on front-end when enabled, positioned right-center
4. Sienna button appears on front-end when enabled, stacked with VLibras button
5. Both widgets work simultaneously without JS errors or style conflicts
6. All Sienna assets load from the plugin's own directory (no CDN requests for Sienna)
7. VLibras script and runtime assets load directly from vlibras.gov.br when enabled
8. Zero PHPCS errors (WordPress-Extra standard)
9. Zero Plugin Check (PCP) errors
10. All user-facing strings are internationalized
11. `readme.txt` is complete and accurate
12. No tool attribution anywhere in the codebase
