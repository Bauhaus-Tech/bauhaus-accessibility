# W-015 — Sienna inline offset override

## Coverage-gap baseline

The permanent browser proof and the strengthened stylesheet assertion were
added after the production declaration already used 20px offsets. Their first
run therefore passed:

```sh
vendor/bin/phpunit --filter test_plugin_styles_override_inline_sienna_offsets_to_align_with_vlibras
```

Result: **1 test, 5 assertions, 0 failures**.

```sh
BAUHAUS_LOCAL_SITE_ORIGIN=http://centrodememoria.local \
BAUHAUS_LOCAL_SITE_IP=172.20.207.188 \
NODE_PATH=/tmp/bauhaus-sienna-source/node_modules \
node team/WORKITEMS/W-015/local-position-proof.cjs
```

Result: the site exposed `WordPress 7.1`; with inline 10px offsets, the browser
computed `left: 20px` in left mode and `right: 20px` in right mode.

## Sensitivity probe

The two Sienna declarations were temporarily changed from `20px !important` to
`10px !important` without changing any test. The full PHPUnit suite then
reported **43 tests, 105 assertions, 1 failure** in
`test_plugin_styles_override_inline_sienna_offsets_to_align_with_vlibras`.
The browser proof failed independently with `10px !== 20px` for left mode.
The two declarations were restored to `20px !important` with a targeted patch.
