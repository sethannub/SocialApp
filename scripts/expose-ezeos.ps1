# Makes https://social.y5media.net live: locks owner registration to one email, then adds the
# hostname to the existing leads-y5media Cloudflare tunnel (route + DNS) and restarts it.
# The CRM (leads.y5media.net) shares that tunnel: expect a few seconds' blip on restart.
# Usage: .\scripts\expose-ezeos.ps1 -OwnerEmail you@y5media.net
param([Parameter(Mandatory = $true)][string]$OwnerEmail)
$ErrorActionPreference = 'Stop'
if ($OwnerEmail -notmatch '^[^@\s]+@[^@\s]+\.[^@\s]+$') { throw "Not an email address: $OwnerEmail" }

$script = @'
set -e
ENVF=/opt/socialapp/src/app/.env
CFG=/etc/cloudflared/config-leads.yml
TUNNEL=99d3b6eb-8653-4136-9614-41f764993efc
HOST=social.y5media.net

# 1. Only the owner email may register first; recreate the container so it reads the new .env
sed -i "s/^INSTANCE_OWNER_EMAIL=.*/INSTANCE_OWNER_EMAIL=__OWNER__/" "$ENVF"
grep -q '^INSTANCE_OWNER_EMAIL=__OWNER__$' "$ENVF" || { echo "owner email not set"; exit 1; }
cd /opt/socialapp/src/app
docker compose -p socialapp -f docker-compose.development.yaml up -d --force-recreate 2>&1 | tail -3
for i in $(seq 1 40); do curl -fsS -o /dev/null http://127.0.0.1:8095/up && break; sleep 3; done
curl -fsS -o /dev/null http://127.0.0.1:8095/up && echo "app up with owner lock"

# 2. Tunnel route (once), validated before restart
if ! grep -q "hostname: $HOST" "$CFG"; then
  cp -p "$CFG" "$CFG.bak-$(date +%Y%m%d%H%M%S)"
  sed -i "s|  - service: http_status:404|  - hostname: $HOST\n    service: http://127.0.0.1:8095\n  - service: http_status:404|" "$CFG"
fi
cloudflared tunnel --config "$CFG" ingress validate
cloudflared --origincert /root/.cloudflared/cert.pem tunnel route dns "$TUNNEL" "$HOST" || echo "(DNS record may already exist)"
systemctl restart cloudflared-leads
sleep 5
systemctl is-active cloudflared-leads

# 3. Check both sites through Cloudflare
for i in $(seq 1 20); do
  code=$(curl -s -o /dev/null -w "%{http_code}" "https://$HOST/login" || true)
  [ "$code" = "200" ] && break; sleep 3
done
echo "social: $(curl -s -o /dev/null -w "%{http_code}" "https://$HOST/login")"
echo "crm:    $(curl -s -o /dev/null -w "%{http_code}" https://leads.y5media.net/)"
'@
$script.Replace('__OWNER__', $OwnerEmail) | ssh -o BatchMode=yes ezeos-live "tr -d '\r' | bash -s"
