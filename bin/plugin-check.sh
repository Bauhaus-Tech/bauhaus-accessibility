#!/usr/bin/env bash
#
# Runs the official WordPress Plugin Check (PCP) against this plugin locally,
# inside throwaway Docker containers — the same checks WordPress.org runs on
# submission and the same the CI workflow runs on every push/PR.
#
# Requires: Docker + the Compose plugin. No local PHP/WordPress needed.
#
# Usage:
#   bin/plugin-check.sh                 # human-readable table
#   bin/plugin-check.sh --format=json   # machine-readable
#   bin/plugin-check.sh --categories=plugin_repo   # only WP.org repo checks
#
# Any extra args are forwarded to `wp plugin check`.
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE_FILE="$ROOT/bin/plugin-check.compose.yml"
SLUG="bauhaus-accessibility"

# Check the *distributed* plugin (what ships to WordPress.org), not the dev
# tree — otherwise PCP flags bin/, .github, docs, etc. that .distignore
# already excludes. bin/build-zip.sh produces build/<slug>/ with runtime files.
echo "==> Building distributable plugin (build/$SLUG)..."
bash "$ROOT/bin/build-zip.sh" >/dev/null
export PLUGIN_DIR="$ROOT/build/$SLUG"

compose() { docker compose -f "$COMPOSE_FILE" "$@"; }
# Call the wp-cli phar directly with a roomy memory limit (core download +
# Plugin Check are memory-hungry and the image default is 128M).
wp() { compose exec -T -u root wpcli php -d memory_limit=512M /usr/local/bin/wp --allow-root --path=/var/www/html "$@"; }

cleanup() { compose down -v --remove-orphans >/dev/null 2>&1 || true; }
trap cleanup EXIT

echo "==> Starting WordPress + DB containers..."
compose up -d --wait

echo "==> Installing WordPress core..."
wp core download --force >/dev/null
wp config create \
	--dbhost=db --dbname=wordpress --dbuser=root --dbpass=root --force >/dev/null
wp core install \
	--url=http://localhost --title="Plugin Check" \
	--admin_user=admin --admin_password=admin --admin_email=admin@example.com \
	--skip-email >/dev/null

echo "==> Installing the Plugin Check (PCP) plugin..."
wp plugin install plugin-check --activate >/dev/null

echo "==> Running Plugin Check on '$SLUG'..."
echo
# Default args produce a readable table; caller args override/extend.
if [ "$#" -eq 0 ]; then
	set -- --severity=5
fi
wp plugin check "$SLUG" "$@"
