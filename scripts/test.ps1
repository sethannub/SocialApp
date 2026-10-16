# Runs Shoutrrr's test suite in a throwaway Docker container.
# The shoutrrr:dev image has no dev packages, so run-tests.sh installs them inside the
# container only; the running app and its data are not touched.
# The working-tree PHP code (app, config, database, routes, tests) is mounted over the image's
# copy, so tests see uncommitted edits without a rebuild. public/ (icons, built assets) comes from
# the image, so after changing files in app/public rebuild the image first.
# Result: scripts/out/pest.txt (git-ignored).
$root = Split-Path $PSScriptRoot -Parent
$out = Join-Path $PSScriptRoot 'out'
New-Item -ItemType Directory -Force $out | Out-Null
Copy-Item (Join-Path $PSScriptRoot 'run-tests.sh') (Join-Path $out 'run-tests.sh') -Force
docker run --rm --entrypoint sh `
    -v "$root\app\app:/var/www/html/app:ro" `
    -v "$root\app\config:/var/www/html/config:ro" `
    -v "$root\app\database:/var/www/html/database:ro" `
    -v "$root\app\routes:/var/www/html/routes:ro" `
    -v "$root\app\resources:/var/www/html/resources:ro" `
    -v "$root\app\tests:/var/www/html/tests:ro" `
    -v "$root\app\phpunit.xml:/var/www/html/phpunit.xml:ro" `
    -v "${out}:/out" `
    -e APP_ENV=testing `
    -e "PINT_FILES=$env:PINT_FILES" `
    shoutrrr:dev /out/run-tests.sh
Get-Content (Join-Path $out 'pest.txt') | ForEach-Object { $_ -replace '\x1b\[[0-9;]*m', '' } | Select-String -Pattern 'FAILED|Tests:|pest exit|pint exit|FAIL |✓|⨯' | ForEach-Object { $_.Line.Trim() }
