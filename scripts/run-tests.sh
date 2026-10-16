#!/bin/sh
# Runs Shoutrrr's Pest suite inside a throwaway container (dev deps installed only here).
cd /var/www/html
touch .env
export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
composer install --no-interaction --no-scripts --quiet 2>&1 | tail -5
php -d memory_limit=2G vendor/bin/pest --compact > /out/pest.txt 2>&1
echo "pest exit: $?" >> /out/pest.txt
# Style check (no writes - code is mounted read-only); files listed in $PINT_FILES, if any
if [ -n "$PINT_FILES" ]; then
  vendor/bin/pint --test $PINT_FILES >> /out/pest.txt 2>&1
  echo "pint exit: $?" >> /out/pest.txt
fi
