# Manual tests

## MT-01 — Government-hosted VLibras widget

**Preconditions:** An administrator has enabled the VLibras interpreter in Settings → Accessibility and selected either the left or right widget position.

**Steps:** 1. Open a public page of the site. 2. Find the VLibras button on the selected side of the page. 3. Select the button.

**Expected result:** The VLibras button is visible on the selected side and opens the Libras interpretation interface.

**Failure modes to try:** Disable the VLibras interpreter and reload the public page; the VLibras button must no longer appear. If the government VLibras service cannot be reached, the interpreter cannot open; the page and the Sienna widget, if enabled, remain usable.

## MT-02 — WordPress.org Brazilian Portuguese language pack

**Preconditions:** An approved Brazilian Portuguese language pack is available for the published plugin version, and the site language is set to Portuguese (Brazil).

**Steps:** 1. In the WordPress administration updates screen, install available translations. 2. Open Settings → Accessibility. 3. Review the settings page labels and the Accessibility menu label.

**Expected result:** The plugin interface is shown in Brazilian Portuguese from the WordPress.org language pack. No translation file exists in the installed plugin's `languages` directory.

**Failure modes to try:** Use a site language with no approved plugin language pack and reload the settings page; the interface falls back to English while the plugin remains usable.

## MT-03 — Local Sienna accessibility toolbar

**Preconditions:** An administrator has enabled the Sienna accessibility toolbar in Settings → Accessibility and selected either the left or right widget position.

**Steps:** 1. Open a public page of the site. 2. Find the blue accessibility button on the selected side. 3. Select the button. 4. Use the high-contrast and readable-font controls.

**Expected result:** The compact button aligns with the VLibras control when both use the same side, opens the toolbar, both controls change the current page, and the readable font is applied without relying on an external Sienna service.

**Failure modes to try:** Disable the Sienna toolbar and reload the public page; its button must no longer appear while VLibras, if enabled, remains usable.
