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
- The new right-position test initially passed because right-position support already existed: `vendor/bin/phpunit --filter test_init_script_preserves_right_position_with_government_root_path tests/Frontend/VlibrasWidgetTest.php` reported 1 test, 1 assertion.
- Sensitivity probe: a temporary mutation that always assigned the left-side code caused `vendor/bin/phpunit tests/Frontend/VlibrasWidgetTest.php` to report 9 tests, 12 assertions, and 1 error. The error was the expectation for the right-side initializer, which the mutated implementation did not call.
- Restoration: the saved production file was restored without a Git reset, and `vendor/bin/phpunit tests/Frontend/VlibrasWidgetTest.php` then passed: 9 tests, 12 assertions.

### R3 — No bundled VLibras script

- RED: `vendor/bin/phpunit --filter test_plugin_does_not_include_a_local_vlibras_script tests/Frontend/VlibrasWidgetTest.php` failed with one failure because `assets/js/vlibras-plugin.js` existed.
- GREEN: the same command passed after removal: 1 test, 1 assertion.

## Browser evidence

- A headless browser verification used route `http://terraetravel.local/`,
  mapped to the local loopback server for the test. Viewport: 1280×720.
- Enabled left: the widget loaded from
  `https://vlibras.gov.br/app/vlibras-plugin.js?ver=1.0.0`, requested its
  gov.br icon, popup, and chunk assets, rendered on the left, and opened its
  panel by pointer. There were no page errors.
- Enabled right: the same remote script and assets loaded, the widget rendered
  on the right, and pointer activation opened its panel. There were no page
  errors.
- Disabled: no `[vw]` markup, VLibras script tag, or request to `vlibras.gov.br`
  appeared. There were no page errors.
- Keyboard observation: the government widget's supplied access button did not
  receive focus and Enter did not open its panel in either enabled state. This
  is recorded for an owner decision; no third-party widget behavior was changed
  in PHASE-07.
