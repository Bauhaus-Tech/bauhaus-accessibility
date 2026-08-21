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
