# Copies platform app keys from C:\Codex\SocialApp\keys.env (git-ignored, filled in by the user)
# into the server's .env, restarts the app, then deletes the local file. Values are never printed.
# keys.env lines, e.g.:
#   FACEBOOK_CLIENT_ID=...
#   FACEBOOK_CLIENT_SECRET=...
#   LINKEDIN_CLIENT_ID=...
#   LINKEDIN_CLIENT_SECRET=...
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$file = Join-Path $root 'keys.env'
if (-not (Test-Path $file)) { throw "Create $file first (one KEY=value per line)." }

$allowed = 'FACEBOOK_CLIENT_ID', 'FACEBOOK_CLIENT_SECRET', 'LINKEDIN_CLIENT_ID', 'LINKEDIN_CLIENT_SECRET', 'X_CLIENT_ID', 'X_CLIENT_SECRET'
$lines = @()
foreach ($raw in Get-Content $file) {
    $line = $raw.Trim()
    if ($line -eq '' -or $line.StartsWith('#')) { continue }
    $name = ($line -split '=', 2)[0].Trim()
    $value = if ($line.Contains('=')) { ($line -split '=', 2)[1].Trim() } else { '' }
    if ($allowed -notcontains $name) { throw "Unknown key '$name'. Allowed: $($allowed -join ', ')" }
    if ($value -eq '' -or $value -match '\s') { throw "$name is empty or contains spaces." }
    $lines += "$name=$value"
}
if ($lines.Count -eq 0) { throw 'keys.env has no keys.' }
"Sending: $((($lines | ForEach-Object { ($_ -split '=', 2)[0] }) -join ', '))"

$tmp = Join-Path $root 'scripts\out\keys.upload'
[IO.File]::WriteAllText($tmp, (($lines -join "`n") + "`n"), (New-Object System.Text.UTF8Encoding($false)))
scp -q -o BatchMode=yes $tmp ezeos-live:/root/socialapp-keys.upload
Remove-Item $tmp -Force
if ($LASTEXITCODE -ne 0) { throw 'scp failed' }

$merge = @'
set -e
ENVF=/opt/socialapp/src/app/.env
UP=/root/socialapp-keys.upload
chmod 600 "$UP"
while IFS='=' read -r k v; do
  [ -z "$k" ] && continue
  if grep -q "^$k=" "$ENVF"; then
    awk -v k="$k" -v v="$v" 'BEGIN{FS=OFS="="} $1==k {print k"="v; next} {print}' "$ENVF" > "$ENVF.tmp" && mv "$ENVF.tmp" "$ENVF"
  else
    printf '%s=%s\n' "$k" "$v" >> "$ENVF"
  fi
  echo "set $k"
done < "$UP"
shred -u "$UP" 2>/dev/null || rm -f "$UP"
chmod 600 "$ENVF"
cd /opt/socialapp/src/app
docker compose -p socialapp -f docker-compose.development.yaml up -d --force-recreate 2>&1 | tail -2
for i in $(seq 1 40); do curl -fsS -o /dev/null http://127.0.0.1:8095/up && break; sleep 3; done
curl -fsS -o /dev/null http://127.0.0.1:8095/up && echo "app restarted with the new keys"
'@
$merge | ssh -o BatchMode=yes ezeos-live "tr -d '\r' | bash -s"
Remove-Item $file -Force
"Local keys.env deleted."
