#!/usr/bin/env bash
#
# Builds an installable WordPress plugin zip containing only the runtime files.
# Dev tooling (bin, README.md, CI) is excluded via .distignore.
#
# Output: <slug>-<version>.zip at the repo root, with a top-level <slug>/ folder
# as WordPress expects.
#
set -euo pipefail

# WordPress.org slug (= zip top folder, where the plugin is installed).
SLUG="bauhaus-accessibility"
# The main plugin file.
PLUGIN_FILE="bauhaus-accessibility.php"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

VERSION="$(grep -m1 '^Stable tag:' "$ROOT/readme.txt" | sed 's/.*Stable tag:[[:space:]]*//' | tr -d '\r')"
if [ -z "$VERSION" ]; then
	echo "Could not read 'Stable tag' from readme.txt" >&2
	exit 1
fi

BUILD="$ROOT/build"
DEST="$BUILD/$SLUG"
ZIP="$ROOT/$SLUG-$VERSION.zip"

rm -rf "$BUILD"
mkdir -p "$DEST"

# Copy all files, then remove what .distignore excludes.
rsync -av --exclude-from="$ROOT/.distignore" "$ROOT/" "$DEST/"

# Drop dev-only placeholders that should not ship.
find "$DEST" -name '.gitkeep' -delete

rm -f "$ZIP"
( cd "$BUILD" && zip -rq "$ZIP" "$SLUG" )

echo "Built: $ZIP"
unzip -l "$ZIP"
