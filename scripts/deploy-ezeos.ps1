# Deploys the committed HEAD of app/ to EZEOS (/opt/socialapp/src/app) and (re)builds the
# container. Server settings live in /opt/socialapp/src/app/.env on the server (never in git).
# Usage: .\scripts\deploy-ezeos.ps1            (normal deploy)
#        .\scripts\deploy-ezeos.ps1 -FirstRun  (also writes the server .env if missing, enables Docker)
param([switch]$FirstRun)
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
Set-Location $root

if ((git status --porcelain -- app) -ne $null) { throw 'app/ has uncommitted changes - commit first (the deploy ships HEAD).' }
$sha = (git rev-parse --short HEAD).Trim()
"Shipping $sha ..."

# 1. Ship the code (no .env, no node_modules: git archive only contains committed files)
$tar = Join-Path $root 'scripts\out\deploy.tar'
git archive --format=tar -o $tar HEAD app
# PowerShell 5.1 cannot pipe binary into native commands, so copy the archive with scp.
scp -q -o BatchMode=yes $tar ezeos-live:/tmp/socialapp-deploy.tar
if ($LASTEXITCODE -ne 0) { throw 'scp failed' }
ssh -o BatchMode=yes ezeos-live 'set -e; mkdir -p /opt/socialapp/src; rm -rf /opt/socialapp/src/app.new; mkdir -p /opt/socialapp/src/app.new; tar -x -f /tmp/socialapp-deploy.tar -C /opt/socialapp/src/app.new; if [ -f /opt/socialapp/src/app/.env ]; then cp -p /opt/socialapp/src/app/.env /opt/socialapp/src/app.new/app/.env; fi; rm -rf /opt/socialapp/src/app; mv /opt/socialapp/src/app.new/app /opt/socialapp/src/app; rmdir /opt/socialapp/src/app.new; rm -f /tmp/socialapp-deploy.tar'
if ($LASTEXITCODE -ne 0) { throw 'unpack on the server failed' }
"Code shipped."

# 2. First run: server .env (fresh APP_KEY generated ON the server) + Docker on boot
if ($FirstRun) {
    $script = @'
set -e
ENVF=/opt/socialapp/src/app/.env
if [ ! -f "$ENVF" ]; then
  umask 077
  cat > "$ENVF" <<EOF
APP_NAME="Y5Media Social"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:$(openssl rand -base64 32)
APP_URL=https://social.y5media.net
APP_PORT=127.0.0.1:8095
OCTANE_HTTPS=true
TRUSTED_PROXIES=*
SESSION_SECURE_COOKIE=true
SELF_HOSTED=true
APP_DISPLAY_TIMEZONE=Asia/Dubai
INSTANCE_PLATFORMS_DISABLED=bluesky,discord,threads
INSTANCE_OWNER_EMAIL=
MAIL_MAILER=log
FEEDBACK_ENABLED=false
SOCIALITE_ENABLED=false
# Platform app keys (filled in later):
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI=https://social.y5media.net/accounts/callback/meta
LINKEDIN_CLIENT_ID=
LINKEDIN_CLIENT_SECRET=
LINKEDIN_REDIRECT_URI=https://social.y5media.net/accounts/callback/linkedin
X_CLIENT_ID=
X_CLIENT_SECRET=
X_REDIRECT_URI=https://social.y5media.net/accounts/callback/x
EOF
  echo ".env written (APP_KEY generated on the server)"
else
  echo ".env already exists - left unchanged"
fi
systemctl enable --now docker
systemctl is-active docker
'@
    $script.Replace("`r`n", "`n") | ssh -o BatchMode=yes ezeos-live 'bash -s'
}

# 3. Build and (re)start
$up = @'
set -e
cd /opt/socialapp/src/app
docker compose -p socialapp -f docker-compose.development.yaml up -d --build 2>&1 | tail -15
for i in $(seq 1 60); do
  if curl -fsS -o /dev/null http://127.0.0.1:8095/up; then echo "app is up"; exit 0; fi
  sleep 3
done
echo "app did not answer on 127.0.0.1:8095/up"; docker compose -p socialapp -f docker-compose.development.yaml logs --tail 40; exit 1
'@
$up.Replace("`r`n", "`n") | ssh -o BatchMode=yes ezeos-live 'bash -s'
"Deployed $sha."
