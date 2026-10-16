#!/bin/sh
# Runs Shoutrrr's Pest suite inside a throwaway container (dev deps installed only here).
cd /var/www/html
touch .env
export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
composer install --no-interaction --no-scripts --quiet 2>&1 | tail -5
php -d memory_limit=2G vendor/bin/pest --compact ${PEST_FILTER:-} > /out/pest.txt 2>&1
echo "pest exit: $?" >> /out/pest.txt
# Style check (no writes - code is mounted read-only); files listed in $PINT_FILES, if any
if [ -n "$PINT_FILES" ]; then
  vendor/bin/pint --test $PINT_FILES >> /out/pest.txt 2>&1
  echo "pint exit: $?" >> /out/pest.txt
  # Larastan (Shoutrrr's level-7 static analysis) on the same files, app code only
  STAN_FILES=$(for f in $PINT_FILES; do case "$f" in app/*|config/*) echo "$f";; esac; done)
  if [ -n "$STAN_FILES" ]; then
    vendor/bin/phpstan analyse --no-progress --memory-limit=2G $STAN_FILES >> /out/pest.txt 2>&1
    echo "phpstan exit: $?" >> /out/pest.txt
  fi
fi
