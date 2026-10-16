#!/bin/sh
# Runs Shoutrrr's frontend checks inside a throwaway container. The working tree is copied in
# (not mounted read-write) so node_modules never lands on the Windows side.
set -u
mkdir -p /work && cd /work
tar -C /src --exclude=node_modules --exclude=vendor --exclude=public/build -cf - . | tar -xf -
bun install --frozen-lockfile > /out/js.txt 2>&1 || bun install >> /out/js.txt 2>&1
cp -r /var/www/html/vendor ./vendor 2>/dev/null || true
# Wayfinder route/action helpers (resources/js/actions, resources/js/routes) are generated, not committed.
touch .env
export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)" DB_CONNECTION=sqlite DB_DATABASE=:memory:
php artisan wayfinder:generate --with-form >> /out/js.txt 2>&1
{
  echo "=== vitest"; bun x vitest run > /out/vitest-full.txt 2>&1; tail -6 /out/vitest-full.txt; echo "generated helpers in /work: $(ls resources/js/actions 2>/dev/null | wc -l) entries"
  echo "=== oxlint"; bun x oxlint 2>&1 | tail -15
  echo "=== oxfmt"; bun x oxfmt --check resources/ 2>&1 | tail -15
  echo "=== tsc"; bun x tsc --noEmit 2>&1 | tail -20; echo "tsc exit: $?"
} >> /out/js.txt 2>&1
