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
- The plugin-header test initially passed because the existing header correctly
  declared `bauhaus-accessibility`. Its sensitivity probe temporarily changed
  the header to `temporary-domain`; `vendor/bin/phpunit
  tests/Core/PluginBootstrapTest.php` then failed with 1 failure at the header
  assertion. Restoring the header returned the test to green with 2 tests and
  3 assertions.
- The translation-artifact enumeration test initially passed because the only
  repository translation artifact was the Brazilian Portuguese source PO. Its
  sensitivity probe temporarily added `tests/Core/unexpected-translation.po`;
  `vendor/bin/phpunit tests/Core/TranslationPackagingTest.php` then failed with
  1 failure showing that extra path. Removing the probe returned the test to
  green with 2 tests and 20 assertions.

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

### Browser verification

- The local WordPress site at `http://centrodememoria.local` was checked in a
  1280×720 Chromium session using a disposable administrator account. The
  workspace plugin was made available through a temporary symlink, activated
  for the check, and then deactivated again.
- A compiled `bauhaus-accessibility-pt_BR.mo` was temporarily placed in
  WordPress's `wp-content/languages/plugins/` directory. With the site language
  set to Brazilian Portuguese, the Settings → Accessibility screen displayed:
  `Acessibilidade`, `Configurações do widget`, `Ativar intérprete de língua de
  sinais VLibras`, `Ativar widget de acessibilidade`, `Posição do widget`,
  `Direita`, and `Esquerda`.
- Keyboard navigation from the settings screen focused WordPress's visible
  “Pular para o conteúdo principal” link. The captured screen was visually
  inspected during the run. The temporary language pack and temporary plugin
  symlink were removed after verification; the site language was already
  `pt_BR` and was left unchanged.

### Final verification

- `composer test` passed with 37 tests and 79 assertions.
- `composer lint` completed successfully with no output.
- `composer analyse` completed with 0 errors.
- `msgfmt --check` accepted the Brazilian Portuguese source PO, and
  `bin/build-zip.sh` produced the installable ZIP successfully.

### Browser-test receipt

- Runtime: WordPress 7.1, plugin version 1.0.0, Chromium at a 1280×720
  viewport; route: `http://centrodememoria.local/wp-admin/options-general.php?page=bauhaus-accessibility`.
- Pack provenance: the source PO had SHA-256
  `84888aedda60797eb4f1609312abb1f5426ca01d41fdfa6c59816887101af7d2`.
  `msgfmt` generated the temporary MO with SHA-256
  `3338f261747a23ff43826aae4b606461747503a8bce079fffedb643324fa69e5`.
- Procedure: compile the tracked PO; temporarily install the MO at
  `wp-content/languages/plugins/bauhaus-accessibility-pt_BR.mo`; set the site
  language to `pt_BR` only if necessary; activate the plugin; open the settings
  route; check the translated labels and keyboard focus; deactivate the plugin
  if it was initially inactive; remove the temporary MO and plugin symlink.
- Result: every expected Portuguese label and the skip-link focus check passed.
  The temporary plugin was initially inactive, the site language was already
  `pt_BR`, and both temporary filesystem paths were confirmed absent after the
  check. The administrator screenshot was deliberately not retained because it
  is generated local-environment evidence rather than a distributable asset.
