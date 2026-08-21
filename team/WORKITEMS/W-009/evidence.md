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
- GREEN: the same command passed with 1 test and 8 assertions after removing
  the packaged artifacts, adding the Brazilian Portuguese source PO under
  `docs/translations/`, and completing the PO metadata.

### Review hardening

- The bootstrap callback test initially passed because the existing callback
  was correct. For the required sensitivity probe, the production file was
  copied to `/tmp`, its `plugins_loaded` callback was temporarily changed to
  `__return_null`, and `composer test` then failed with 1 error: the expected
  `bauhaus_accessibility_init` callback was called 0 times. The original file
  was restored from the copy and `composer test` returned to green.
- RED: the archive integration test failed because the build script did not
  create the requested isolated ZIP path.
- GREEN: `vendor/bin/phpunit tests/Core/TranslationPackagingTest.php` passed
  with 2 tests and 18 assertions after the build script accepted isolated
  output paths and `.distignore` excluded all Git-only materials.
- The hidden-root-entry assertion initially passed against the existing
  exclusion rule. For its sensitivity probe, that rule was temporarily removed
  from a saved copy of `.distignore`; the archive test failed with 1 failure.
  Restoring the saved file returned the packaging test to green.

### Quality-gate evidence

- PO validation: `msgfmt --check --output-file=/tmp/bauhaus-accessibility-pt_BR.mo docs/translations/bauhaus-accessibility-pt_BR.po` completed with no output and status 0.
- Lint: `composer lint` completed with no output and status 0.
- Static analysis: `composer analyse` completed with 0 errors. It required the
  unrestricted environment because PHPStan could not bind its local worker
  socket in the sandbox.
- Distribution: `bin/build-zip.sh` completed successfully. The archive
  integration test also built an isolated ZIP and found no `languages/`,
  `docs/translations/`, translation workflow, manual-test, team, or runtime
  metadata paths.
- Regression: `composer test` passed with 36 tests and 75 assertions after
  the review fixes.
