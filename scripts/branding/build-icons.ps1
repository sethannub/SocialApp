# Regenerates every icon in app/public from scripts/branding/icon-master.html.
# 1. headless Chrome renders two 1024px masters (rounded tile, full-bleed tile)
# 2. make-icons.php (run in the shoutrrr:dev image, which has GD) resizes them and builds favicon.ico
# 3. favicon.svg is written from the same mark
$root = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$work = Join-Path $root 'scripts\out\icons'
New-Item -ItemType Directory -Force $work | Out-Null

$chrome = @(
    "$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
    "${env:ProgramFiles(x86)}\Google\Chrome\Application\chrome.exe",
    "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe"
) | Where-Object { Test-Path $_ } | Select-Object -First 1

$page = 'file:///' + ((Join-Path $PSScriptRoot 'icon-master.html') -replace '\\', '/')
foreach ($variant in 'round', 'full') {
    & $chrome --headless=new --disable-gpu --hide-scrollbars --default-background-color=00000000 `
        --window-size=1024,1024 --user-data-dir="$work\profile" `
        "--screenshot=$work\master-$variant.png" "$page#$variant" 2>$null | Out-Null
}

docker run --rm --entrypoint php `
    -v "${work}:/masters" -v "$root\app\public:/public" -v "${PSScriptRoot}:/scripts:ro" `
    shoutrrr:dev /scripts/make-icons.php /masters /public

$mark = 'M133,76.9h6.4c12.9,0,23.3-10.4,23.3-23.3c0-12.9-10.4-23.3-23.3-23.3H133l-24,0.4v-8.5h53.9V7.7H109L91.3,7.9v36.9l6.7,0.1l40.8,0.2c4.3,0,7.9,3.5,7.9,7.9c0,4.4-3.5,7.9-7.9,7.9H91.1l0,15.9L70.8,53.3l0,23.3L133,76.9z'
$mark2 = 'M109.6,7.7l-25.4,0L59.6,36.5L37,7.6H10.7l37.7,45.6v23.5c0,0,9.4,0,22.4,0V53.3l15-18.8L109.6,7.7z'
$svg = @"
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <rect width="512" height="512" rx="112" fill="#0b261b"/>
  <g transform="translate(81 173.5) scale(2.2293)" fill="#a6e72a">
    <path transform="translate(-8 -5)" d="$mark"/>
    <path transform="translate(-8 -5)" d="$mark2"/>
  </g>
</svg>
"@
[IO.File]::WriteAllText((Join-Path $root 'app\public\favicon.svg'), $svg, (New-Object System.Text.UTF8Encoding($false)))
"favicon.svg written"
