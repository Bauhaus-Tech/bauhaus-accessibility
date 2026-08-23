# PHASE-14 — WordPress 7.1 compatibility declaration

## Goal

The plugin's published metadata and project documentation state that it has
been tested through the current WordPress 7.1 release.

## In scope

1. R1: The plugin header declares WordPress 7.1 as the tested version.
2. R2: The WordPress.org readme declares WordPress 7.1 as the tested version.
3. R3: The project README documents the tested-through version.
4. R4: A permanent test prevents the two distribution metadata declarations
   from drifting from WordPress 7.1.

## Out of scope

- Changing the minimum supported WordPress or PHP versions.
- Changing runtime compatibility behavior.
- Updating the plugin release version.

## Acceptance criteria

| # | Criterion | Verified by |
|---|-----------|-------------|
| A1 | Both distribution metadata files declare `Tested up to: 7.1`. | `PluginBootstrapTest::test_distribution_metadata_declares_wordpress_7_1_as_tested` |
| A2 | The project README states the plugin is tested through WordPress 7.1. | `PluginBootstrapTest::test_distribution_metadata_declares_wordpress_7_1_as_tested` |

## Tooling exemption

The following files claim the §2.1 tooling exemption because their headers and
documentation declare metadata rather than runtime behavior:

- `bauhaus-accessibility.php` — plugin header metadata.
- `readme.txt` — WordPress.org distribution metadata.
- `README.md` — human-facing compatibility documentation.

## Risks / open questions

- `Tested up to` records a compatibility-testing claim; it does not change the
  plugin's minimum WordPress version or enforce a runtime version check.

## Phase report

- Process exception: this phase specification was committed together with its
  implementation, so Git history cannot demonstrate that the specification
  existed before the phase began. The compatibility declaration itself is
  covered by the permanent metadata test.
- Verification: PHPUnit reported 42 passing tests and 101 assertions; PHPCS
  reported no errors or warnings; PHPStan reported no errors.
- Review: the independent review identified the phase-specification ordering
  issue above. This report records the agreed resolution; future phase
  specifications must be committed before their implementation begins.
