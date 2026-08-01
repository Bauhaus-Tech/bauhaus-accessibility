# PHASE-01 — Scaffold & Bootstrap

## Goal
A plugin skeleton that activates without errors, registers its presence, and
passes basic WordPress.org structural checks. No user-visible behavior yet —
just a working, testable foundation.

## In scope

- R1: Plugin header with name, URI, description, version, author, license, text domain
- R2: GPLv2+ license block and ABSPATH guard in the main plugin file
- R3: `readme.txt` with valid headers (name, contributors, tags, requires, tested, license, stable tag)
- R4: `Bauhaus_Acessibilidade\Core\Plugin` class that hooks into WordPress and is
  instantiated by the bootstrap file
- R5: Admin submenu page under Settings → Acessibilidade BR with a title (no controls yet)
- R6: `uninstall.php` that removes the plugin's option from the database
- R7: PHPCS configuration (`phpcs.xml.dist`) targeting WordPress-Extra
- R8: PHPStan configuration (`phpstan.neon.dist`) at a baseline level
- R9: `composer.json` for development dependencies (PHPCS, PHPStan, PHPUnit)
- R10: PHPUnit configuration (`phpunit.xml.dist`) bootstrapped against WordPress
- R11: Autoloader (PSR-4 via Composer, or a manual autoloader registered in the
  bootstrap file) — the plugin must load its classes without `require_once` chains

## Out of scope

- Any front-end widget output
- Any Sienna or VLibras assets
- Actual settings fields (just the empty page)
- Translation files (.pot)
- Full readme content (headers only)

## Acceptance criteria

| # | Criterion | Verified by |
|---|-----------|-------------|
| A1 | Plugin activates on WordPress 6.0+ / PHP 7.4+ without errors | Manual: activate and check no fatals |
| A2 | `readme.txt` passes WordPress.org readme validator headers check | `wp plugin verify-readme` or manual inspection |
| A3 | Settings → Acessibilidade BR shows an admin page with title | Manual: navigate to the page |
| A4 | `uninstall.php` is present and correctly structured | Code review |
| A5 | `phpcs.xml.dist` reports zero errors on the plugin's PHP files | `vendor/bin/phpcs` |
| A6 | `phpstan.neon.dist` reports zero errors on the plugin's PHP files | `vendor/bin/phpstan analyse` |
| A7 | `phpunit.xml.dist` boots and runs a trivial passing test | `vendor/bin/phpunit` |
| A8 | Classes are autoloaded via Composer PSR-4 (plugin boots from a single `require`) | Code review of bootstrap + `composer.json` |
| A9 | No vendor attribution in any file | `grep -r` for tool/model names |

## Tooling exemption

The following files claim the §2.1 tooling exemption (no TDD required):
- `composer.json` — dependency manifest, no behavior
- `phpcs.xml.dist` — coding standards config
- `phpstan.neon.dist` — static analysis config
- `phpunit.xml.dist` — test runner config
- `.gitignore` — filesystem exclusion patterns
- `readme.txt` — metadata/documentation file (has structure but no executable behavior)

## Risks / open questions

- WordPress 7.0+ floor in acceptance criteria: WordPress 7.0 does not exist (current
  stable is 6.7). Using 6.0+ as minimum for broad compatibility until owner confirms.
- PHP 8.2+ floor: reasonable for 2026 but narrows install base. Using 7.4+ in
  `Requires PHP` header for broader compatibility unless owner overrides.
