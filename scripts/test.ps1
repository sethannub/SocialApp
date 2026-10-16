# Runs Shoutrrr's test suite in a throwaway Docker container.
# The shoutrrr:dev image has no dev packages, so run-tests.sh installs them inside the
# container only; the running app and its data are not touched.
# Result: scripts/out/pest.txt (git-ignored).
$root = Split-Path $PSScriptRoot -Parent
$out = Join-Path $PSScriptRoot 'out'
New-Item -ItemType Directory -Force $out | Out-Null
Copy-Item (Join-Path $PSScriptRoot 'run-tests.sh') (Join-Path $out 'run-tests.sh') -Force
docker run --rm --entrypoint sh `
    -v "$root\app\tests:/var/www/html/tests:ro" `
    -v "$root\app\phpunit.xml:/var/www/html/phpunit.xml:ro" `
    -v "${out}:/out" `
    -e APP_ENV=testing `
    shoutrrr:dev /out/run-tests.sh
Get-Content (Join-Path $out 'pest.txt') | ForEach-Object { $_ -replace '\x1b\[[0-9;]*m', '' } | Select-Object -Last 4
