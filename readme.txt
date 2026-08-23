=== Bauhaus Accessibility ===
Contributors: bauhaustech, marvila
Tags: accessibility, vlibras, libras, inclusion, inclusao
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds the VLibras sign language widget and the Sienna accessibility toolbar to your WordPress site. Available in English and Portuguese (Brazil).

== Description ==

Bauhaus Accessibility integrates two accessibility tools into your WordPress site:

* **VLibras Widget** — The Brazilian government's Libras (Brazilian Sign Language) virtual interpreter, helping deaf users access your content.
* **Sienna Accessibility Widget** — A full-featured accessibility toolbar with contrast adjustment, font size control, screen reader, dyslexia-friendly font, and more.

Both widgets appear as stacked buttons on the side of the page. You choose left or right.

== Languages ==

The plugin's WordPress interface is written in English by default. Approved
translations are delivered as WordPress.org language packs according to the
site language setting; the plugin does not include its own WordPress
translation files.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/bauhaus-accessibility` or install directly from the WordPress plugin repository.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to Settings → Accessibility to enable the widgets you want.

== Frequently Asked Questions ==

= Where do the widgets appear? =

They appear as two vertically-stacked buttons, vertically centered on the page. You can choose left or right side in the settings.

= Does VLibras load external resources? =

Yes. When VLibras is enabled, its widget script and runtime assets load directly from `https://vlibras.gov.br`. No VLibras JavaScript is bundled with this plugin.

= Is the Sienna widget fully self-contained? =

Yes. All Sienna JavaScript, fonts, and translations are bundled in the plugin. No external requests are made for Sienna.

== Third-Party Libraries ==

This plugin bundles the following third-party libraries:

* **Bauhaus Sienna Accessibility Widget** (v2.0.1) — MIT License
  JavaScript and fonts are built from https://github.com/Bauhaus-Tech/Sienna-Accessibility-Widget
  with `npm ci` followed by `npm run build`. The public repository contains the
  editable source and build configuration; this plugin packages the resulting
  UMD bundle and its local font files.

* **OpenDyslexic Font** (v3) — SIL Open Font License
  Dyslexia-friendly typeface bundled with Sienna.

== External Services ==

* **VLibras Widget** — When enabled, the plugin loads `https://vlibras.gov.br/app/vlibras-plugin.js` and its runtime assets from `https://vlibras.gov.br` to provide the Libras interpreter. No VLibras code is bundled with this plugin.

== Changelog ==

= 1.0.0 =
* Initial release.
* VLibras widget with enable/disable toggle.
* Sienna Accessibility widget with enable/disable toggle.
* Left/right position selector.
* Settings page under Settings → Accessibility.
