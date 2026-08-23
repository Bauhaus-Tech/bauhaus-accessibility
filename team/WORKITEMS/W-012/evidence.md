# W-012 — Local Sienna fork integration

## RED — packaged local runtime

Before changing production code or runtime files, ran:

```sh
composer test -- --filter 'SiennaWidgetTest|SiennaDistributionTest'
```

Result: **8 tests; 11 assertions; 2 errors; 4 failures**.

- The widget still registered a dummy, inline-only `2.2.333` script instead of
  enqueuing the local `2.0.1` UMD.
- Its configuration still used the obsolete `data-position`, `data-lang`, and
  `data-offset` attributes.
- The PHP integration still contained the reported jsDelivr URL.
- The fork's two font files were not yet packaged beside the bundle.

## GREEN — packaged local runtime

The UMD and fonts were rebuilt from public fork commit
`30f5f83` (`Bauhaus-Tech/Sienna-Accessibility-Widget`) and copied into the
plugin distribution. The source commit also replaces external `pages.dev`
navigation links with local controls before the runtime is built.

Ran:

```sh
composer test -- --filter 'SiennaWidgetTest|SiennaDistributionTest'
```

Result: **8 tests; 16 assertions; 0 failures**.

Runtime file SHA-256 values:

- `assets/js/sienna-accessibility.umd.js`:
  `96e4abe52d9ac08adc9891f567c2370ebc404d34220317af66dc0b6b348acec6`
- `assets/js/fonts/OpenDyslexic3-Regular.woff`:
  `76e3e7773b4bf9626737f25c39f5ebccfc4beae25470fea4f358af232c401e21`
- `assets/js/fonts/OpenDyslexic3-Regular.ttf`:
  `54c5c2129fb7ba2c48fa3cb75379f0ea47cfcc24e20f1956a6c080d1efb480a3`

## Regression and browser evidence

- `composer test`: **40 tests; 88 assertions; 0 failures**.
- `composer lint`: **0 errors; 0 warnings**.
- `vendor/bin/phpstan analyse`: **0 errors**.
- The public fork's `npm test`: **4 tests; 0 failures**. Its browser test
  exercised toolbar controls and confirmed all requests used the local test
  server.
- The local WordPress site at `http://centrodememoria.local/` was exercised
  with Sienna enabled. The toolbar opened, high contrast applied, and the only
  Sienna-related requests were the local UMD and local WOFF font:
  `wp-content/plugins/bauhaus-accessibility/assets/js/sienna-accessibility.umd.js?ver=2.0.1`
  and `wp-content/plugins/bauhaus-accessibility/assets/js/fonts/OpenDyslexic3-Regular.woff`.
