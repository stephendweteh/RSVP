#!/usr/bin/env bash
# Build a ZIP of the check-in QR (CID) email files for cPanel: extract at
# Laravel root to overwrite existing files (no git apply).
#
# Usage:
#   ./scripts/export-check-in-qrcode-cpanel-zip.sh [COMMIT_OR_TAG]
#
#   COMMIT_OR_TAG  Git ref to export from — default: HEAD
#
# Optional env:
#   ZIP_OUT=/path/to/out.zip   — output path (default: repo root check-in-qrcode-cpanel-overwrite.zip)

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

TARGET="${1:-HEAD}"
git rev-parse --verify "$TARGET^{commit}" >/dev/null

FILES=(
  app/Mail/RsvpDecisionMail.php
  app/Services/MailTemplateRenderer.php
  app/Services/RsvpCheckInQrService.php
  resources/views/mail/raw-html.blade.php
)

README_SRC="$ROOT/scripts/check-in-qrcode-cpanel-README.txt"
if [[ ! -f "$README_SRC" ]]; then
  echo "missing $README_SRC" >&2
  exit 1
fi

OUT="${ZIP_OUT:-$ROOT/check-in-qrcode-cpanel-overwrite.zip}"
TMP="$(mktemp -d)"
cleanup() { rm -rf "$TMP"; }
trap cleanup EXIT

git archive --format=tar "$TARGET" -- "${FILES[@]}" | tar -x -C "$TMP"
cp "$README_SRC" "$TMP/README.txt"

rm -f "$OUT"
( cd "$TMP" && zip -r "$OUT" . -x "*.DS_Store" )

echo "Wrote: $OUT"
echo "From:  $TARGET ($(git rev-parse --short "$TARGET^{commit}"))"
unzip -l "$OUT"
