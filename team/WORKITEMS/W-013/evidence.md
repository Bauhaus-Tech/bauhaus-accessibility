# W-013 — Remove legacy Sienna locales

## RED — package boundary

Before removing any runtime files, ran:

```sh
composer test -- --filter TranslationPackagingTest
```

Result: **2 tests; 8 assertions; 2 failures**.

- `assets/locales/` still existed in the repository.
- The generated plugin ZIP still contained `assets/locales/` entries.

The standalone locale directory was then removed. The current Sienna UMD keeps
its required locale modules compiled into the local bundle.

## GREEN — cleaned package

After removal, ran:

```sh
composer test
composer lint
vendor/bin/phpstan analyse
```

Results:

- `composer test`: **40 tests; 92 assertions; 0 failures**.
- `composer lint`: **0 errors; 0 warnings**.
- `vendor/bin/phpstan analyse`: **0 errors**.

## Review follow-up — bundled locale guard

The phase review identified a coverage gap: the ZIP test proved the legacy
directory was gone but did not prove the UMD still embeds its replacement
locale modules. `SiennaDistributionTest` now verifies embedded English and
Portuguese locale modules and the absence of a `fetch(` locale loader.

This was a coverage-gap test: its first run passed with **4 tests; 12
assertions**. A temporary local mutation renamed the UMD's Portuguese locale
module path, producing **1 failure** in exactly the new locale assertion. The
byte-identical UMD was restored before the full suite was run.
