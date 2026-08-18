# W-005 — Load VLibras from gov.br

## Requirement map

| Requirement | Production boundary | Automated proof |
|---|---|---|
| R1 | `VlibrasWidget::maybe_enqueue()` | `VlibrasWidgetTest::test_enabled_widget_enqueues_government_hosted_script()` |
| R2 | `VlibrasWidget::maybe_enqueue()` | `VlibrasWidgetTest::test_init_script_preserves_configured_position_with_government_root_path()` |
| R3 | Plugin assets and installation documentation | `VlibrasWidgetTest::test_plugin_does_not_include_a_local_vlibras_script()` and documentation review |

## TDD evidence

### R1 — Government-hosted widget script

- RED: `vendor/bin/phpunit --filter test_enabled_widget_enqueues_government_hosted_script tests/Frontend/VlibrasWidgetTest.php` failed with one failure because the implementation enqueued the local bundle instead of the expected gov.br URL.
- GREEN: the same command passed: 1 test, 1 assertion.

### R2 — Preserve the configured position

- The strengthened position test initially passed because left-position support already existed: `vendor/bin/phpunit --filter test_init_script_preserves_configured_position_with_government_root_path tests/Frontend/VlibrasWidgetTest.php` reported 1 test, 1 assertion.
- Sensitivity probe: a temporary mutation that always assigned the right-side code caused `vendor/bin/phpunit tests/Frontend/VlibrasWidgetTest.php` to report 7 tests, 10 assertions, and 1 error. The error was the expectation for the left-side initializer, which the mutated implementation did not call.
- Restoration: the saved production file was restored without a Git reset, and `vendor/bin/phpunit tests/Frontend/VlibrasWidgetTest.php` then passed: 7 tests, 10 assertions.

### R3 — No bundled VLibras script

- RED: `vendor/bin/phpunit --filter test_plugin_does_not_include_a_local_vlibras_script tests/Frontend/VlibrasWidgetTest.php` failed with one failure because `assets/js/vlibras-plugin.js` existed.
- GREEN: the same command passed after removal: 1 test, 1 assertion.
