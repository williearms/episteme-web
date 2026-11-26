#!/usr/bin/env bash
# Run the browser (Playwright) tests inside the Docker setup. Extra arguments go to Playwright,
# e.g.  bash scripts/e2e.sh tests/e2e/checkout.spec.mjs
#
# Uses its own "e2e" database and app server (port 8011); your development data is not touched.
set -euo pipefail
cd "$(dirname "$0")/.."
export MSYS_NO_PATHCONV=1 # Git Bash on Windows would otherwise rewrite container paths

docker compose up -d --wait

echo "==> Preparing the e2e database"
docker compose exec -T mysql sh -c 'printf "CREATE DATABASE IF NOT EXISTS e2e; GRANT ALL PRIVILEGES ON e2e.* TO %s@\"%%\";" "$MYSQL_USER" | MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot'

docker compose exec -T laravel.test sh -c 'test -d node_modules/@playwright/test || npm install'

# The browser and its system libraries live in the container, so this runs again if it is recreated.
docker compose exec -T laravel.test sh -c 'test -d /ms-playwright || { echo "==> Installing Chromium (a few hundred MB, once per container)"; PLAYWRIGHT_BROWSERS_PATH=/ms-playwright npx playwright install --with-deps chromium && chmod -R a+rX /ms-playwright; }'

echo "==> Building assets"
docker compose exec -T -e VITE_BUILD_DIR=build-e2e laravel.test npm run build

echo "==> Running Playwright"
docker compose exec -T -u sail -e PLAYWRIGHT_BROWSERS_PATH=/ms-playwright laravel.test npx playwright test "$@"
