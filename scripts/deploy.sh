#!/usr/bin/env bash
#
# Deploy Geprek Geh ke InfinityFree (FTP) — satu tombol.
#
# Cara pakai:
#   cp scripts/.deploy.env.example scripts/.deploy.env   # isi kredensial
#   bash scripts/deploy.sh                               # build + upload
#   bash scripts/deploy.sh --build-only                  # build paket saja (tanpa upload)
#   bash scripts/deploy.sh --dry-run                     # liat file yang akan di-upload
#
# Prasyarat: git, curl, dan di komputermu akses FTP ke infinityfree tidak diblokir.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

DEPLOY_ENV="$ROOT/scripts/.deploy.env"
BUILD_DIR="/tmp/geprekgeh-deploy"
BUILD_TAR="/tmp/geprekgeh-deploy.tar.gz"

MODE_UPLOAD=1
if [[ "${1:-}" == "--build-only" ]]; then MODE_UPLOAD=0; fi
if [[ "${1:-}" == "--dry-run" ]]; then MODE_UPLOAD=0; DRY=1; fi
DRY="${DRY:-0}"

# ----------------------------------------------------------------------
# 1. Baca kredensial
# ----------------------------------------------------------------------
if [[ ! -f "$DEPLOY_ENV" ]]; then
    echo "✗ '$DEPLOY_ENV' tidak ada."
    echo "  cp scripts/.deploy.env.example scripts/.deploy.env"
    exit 1
fi
set -a; source "$DEPLOY_ENV"; set +a

: "${GG_FTP_HOST:?GG_FTP_HOST kosong di .deploy.env}"
: "${GG_FTP_USER:?GG_FTP_USER kosong}"
: "${GG_FTP_PASS:?GG_FTP_PASS kosong}"
: "${GG_FTP_DIR:?GG_FTP_DIR kosong}"

echo "🍗 Deploy Geprek Geh → ${GG_FTP_USER}@${GG_FTP_HOST}/${GG_FTP_DIR}"

# ----------------------------------------------------------------------
# 2. Build paket bersih (isi repo saat ini, tanpa logs/uploads/.env lokal)
# ----------------------------------------------------------------------
rm -rf "$BUILD_DIR" "$BUILD_TAR"
mkdir -p "$BUILD_DIR"
git ls-files -z | tar --null -T - -cf - | (cd "$BUILD_DIR" && tar -xf -)

# Buat .env production dari kredensial .deploy.env
cat > "$BUILD_DIR/.env" <<EOF
GG_DB_HOST=${GG_DB_HOST}
GG_DB_NAME=${GG_DB_NAME}
GG_DB_USER=${GG_DB_USER}
GG_DB_PASS=${GG_DB_PASS}
GG_DB_CHARSET=utf8mb4

GEPREK_ADMIN_NAME=Admin Geprek Geh
GEPREK_ADMIN_EMAIL=admin@geprekgeh.com
GEPREK_ADMIN_PASS=${GEPREK_ADMIN_PASS}
EOF

# Hapus artefak lokal yang tak boleh naik
rm -rf "$BUILD_DIR/logs" "$BUILD_DIR/assets/uploads" "$BUILD_DIR/.playwright-mcp"

FILES=()
while IFS= read -r -d '' f; do
    FILES+=("$f")
done < <(cd "$BUILD_DIR" && find . -type f -print0)
TOTAL=${#FILES[@]}
echo "✓ Paket dibangun: ${TOTAL} file (${BUILD_DIR})"

if [[ "$MODE_UPLOAD" == "0" ]]; then
    if [[ "$DRY" == "1" ]]; then
        printf '%s\n' "${FILES[@]}" | sed 's|^\./|  /|'
    fi
    tar -C /tmp -czf "$BUILD_TAR" geprekgeh-deploy
    echo "✓ (tanpa upload) arsip: $BUILD_TAR"
    exit 0
fi

# ----------------------------------------------------------------------
# 3. Upload via FTP — "FXP-friendly" flags supaya tahan di jaringan CGNAT
# ----------------------------------------------------------------------
FTP_URL="ftp://${GG_FTP_HOST}/${GG_FTP_DIR}"
CURL=(curl -sS --connect-timeout 20 --disable-epsv --ftp-create-dirs -u "${GG_FTP_USER}:${GG_FTP_PASS}")

# tes koneksi kontrol dulu
if ! timeout 30 curl -sS --connect-timeout 20 -u "${GG_FTP_USER}:${GG_FTP_PASS}" --ftp-ssl-control "ftp://${GG_FTP_HOST}/" -o /dev/null 2>/dev/null; then
    echo "⚠ tes login FTP gagal — cek kredensial/jaringan"
fi

i=0
for f in "${FILES[@]}"; do
    rel="${f#./}"
    i=$((i+1))
    printf "[%3d/%3d] %s\n" "$i" "$TOTAL" "/$rel"
    if ! "${CURL[@]}" -T "$BUILD_DIR/$rel" "$FTP_URL/$rel"; then
        echo "✗ gagal upload /$rel"
        exit 1
    fi
done

echo ""
echo "✅ Upload selesai: ${i} file ke ${FTP_URL}"
echo "🔑 DB masih kosong — seed lewat hPanel → MySQL → import database/production.sql,"
echo "   atau jalankan php install.php --empty jika ada terminal hosting."