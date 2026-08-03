# WordPress.org assets

> **The `icon-*.png` and `banner-*.png` files here are placeholders.** Replace
> them with the final artwork, keeping the exact same file names and sizes.

Drop the WordPress.org plugin directory assets here. They are pushed to the SVN
`/assets` folder by the GitHub Actions workflows (`deploy.yml` and
`wordpress-org-assets.yml`) — they do **not** ship inside the plugin zip.

Expected file names (PNG or JPG; SVG allowed for the icon):

| Asset       | File name(s)                                   | Size            |
|-------------|------------------------------------------------|-----------------|
| Icon        | `icon-128x128.png`, `icon-256x256.png` or `icon.svg` | 128² / 256²  |
| Banner      | `banner-772x250.png`                           | 772 × 250       |
| Banner (HiDPI) | `banner-1544x500.png`                       | 1544 × 500      |
| Screenshots | `screenshot-1.png`, `screenshot-2.png`, …      | any             |

Screenshot captions come from the `== Screenshots ==` section of `readme.txt`,
in order.

See: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
