#!/usr/bin/env bash
# Regenerate static HTML snapshots of every PHP page.
# Run this whenever PHP content or CSS changes for the university submission.
#
# Usage: bash generate-html-snapshots.sh
# Requires: Apache + MySQL running, demo@stitchhouse.test user in DB.

set -e
BASE="http://localhost/product"
cd "$(dirname "$0")"

echo "== Rendering public pages =="
PUBLIC_PAGES=(
  index
  order
  myorder
  cart
  checkout
  contact
  login
  shipping-policy
  returns
  size-guide
  faqs
  terms
  privacy
)
for p in "${PUBLIC_PAGES[@]}"; do
  curl -s "${BASE}/${p}.php" -o "${p}.html" --max-time 10
  printf "  %-30s %d bytes\n" "${p}.html" "$(wc -c < ${p}.html)"
done

echo "== Rendering auth-protected pages =="
JAR=$(mktemp)
# Login via CSRF-aware POST
curl -s -c "$JAR" "${BASE}/login.php" -o /tmp/_login.html
TOKEN=$(grep -oP 'name="_csrf" value="\K[^"]+' /tmp/_login.html | head -1)
curl -s -b "$JAR" -c "$JAR" -L \
  -d "form_type=login&_csrf=${TOKEN}&email=demo@stitchhouse.test&password=demo1234" \
  "${BASE}/login.php" -o /tmp/_postlogin.html
# Dashboard (logged-in snapshot)
curl -s -b "$JAR" "${BASE}/dashboard.php" -o dashboard.html
printf "  %-30s %d bytes\n" "dashboard.html" "$(wc -c < dashboard.html)"

# Order confirmation requires a seeded session — use a temporary helper
cat > _snapshot_oc.php <<'PHPEOF'
<?php
session_start();
$_SESSION['order_success'] = true;
$_SESSION['order_id'] = 1001;
$_SESSION['order_details'] = ['total' => 1700];
header("Location: order-confirmation.php");
PHPEOF
OC_JAR=$(mktemp)
curl -s -c "$OC_JAR" "${BASE}/_snapshot_oc.php" -o /dev/null
curl -s -b "$OC_JAR" "${BASE}/order-confirmation.php" -o order-confirmation.html
printf "  %-30s %d bytes\n" "order-confirmation.html" "$(wc -c < order-confirmation.html)"
rm -f _snapshot_oc.php "$JAR" "$OC_JAR" /tmp/_login.html /tmp/_postlogin.html

echo ""
echo "Done. $(ls *.html | wc -l) HTML files generated."
