# Manual tests

## MT-01 — Government-hosted VLibras widget

**Preconditions:** An administrator has enabled the VLibras interpreter in Settings → Accessibility and selected either the left or right widget position.

**Steps:** 1. Open a public page of the site. 2. Find the VLibras button on the selected side of the page. 3. Select the button.

**Expected result:** The VLibras button is visible on the selected side and opens the Libras interpretation interface.

**Failure modes to try:** Disable the VLibras interpreter and reload the public page; the VLibras button must no longer appear. If the government VLibras service cannot be reached, the interpreter cannot open; the page and the Sienna widget, if enabled, remain usable.
