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
