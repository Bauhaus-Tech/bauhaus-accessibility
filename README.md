# Bauhaus Accessibility

WordPress plugin that adds accessibility widgets to your site:

- **VLibras** — Brazilian Sign Language (Libras) virtual interpreter
- **Sienna** — Accessibility toolbar (contrast, font size, screen reader, etc.)

## VLibras widget

When an administrator enables VLibras in Settings → Accessibility, visitors see
the government VLibras interpreter button on the configured left or right side
of each public page. Selecting it opens the Libras interpretation interface.

The plugin loads the official widget script and its runtime assets directly from
`https://vlibras.gov.br` when the widget is enabled; it does not include a copy
of the VLibras JavaScript. If that government service is unavailable, the
VLibras button cannot load, while the rest of the site and the Sienna widget
remain available.

## WordPress interface translations

The plugin's WordPress interface uses the `bauhaus-accessibility` text domain.
WordPress downloads approved interface translations as language packs from
WordPress.org when they are available for the site's language. The plugin does
not ship its own WordPress translation files.

The Brazilian Portuguese source translation and its
[contribution workflow](translations.md) are maintained in this repository for
translation contributors; they are not part of the plugin's WordPress.org
distribution.

## Requirements

- WordPress 6.0+
- PHP 8.2+

## Development

```bash
composer install
vendor/bin/phpunit      # Run tests
vendor/bin/phpcs        # Lint PHP
vendor/bin/phpstan analyse  # Static analysis
```

## License

GPL-3.0-or-later — see [readme.txt](readme.txt) and the plugin header.
