# W-009 — WordPress.org language packs

## Requirement map

| Requirement | Production boundary | Proof |
|---|---|---|
| R1 | `bauhaus-accessibility.php` bootstrap | `tests/Core/PluginBootstrapTest.php` |
| R2 | Plugin distribution files | `tests/Core/TranslationPackagingTest.php` |
| R3 | Translation contribution instructions | `translations.md` review |

## TDD evidence

### R1 — Manual loading is absent

- RED: `vendor/bin/phpunit tests/Core/PluginBootstrapTest.php` failed with
  `Failed asserting that true is false.` at
  `tests/Core/PluginBootstrapTest.php:55`, proving the manual loader function
  was still defined.
- GREEN: `vendor/bin/phpunit tests/Core/PluginBootstrapTest.php` passed with
  1 test and 1 assertion after removing the loader and its `init` hook.

### R2 — Translation artifacts stay outside the package

- RED: `vendor/bin/phpunit tests/Core/TranslationPackagingTest.php` failed
  because the `languages/` directory existed.
- GREEN: the same command passed with 1 test and 5 assertions after removing
  the packaged artifacts and adding the Brazilian Portuguese source PO under
  `docs/translations/`.

### Quality-gate evidence

- PO validation: `msgfmt --check --output-file=/tmp/bauhaus-accessibility-pt_BR.mo docs/translations/bauhaus-accessibility-pt_BR.po` completed with no output and status 0.
- Lint: `composer lint` completed with no output and status 0.
- Static analysis: `composer analyse` completed with 0 errors. It required the
  unrestricted environment because PHPStan could not bind its local worker
  socket in the sandbox.
- Distribution: `bin/build-zip.sh` completed successfully. A subsequent ZIP
  listing check found neither `languages/` nor `docs/translations/`.
