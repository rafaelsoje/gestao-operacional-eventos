#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

MODE="${1:---dry-run}"
if [[ "$MODE" != "--dry-run" && "$MODE" != "--apply" ]]; then
  echo "Uso: $0 [--dry-run|--apply]"
  exit 1
fi

STAMP="$(date +%Y%m%d-%H%M%S)"
QUARANTINE_DIR="writable/quarantine/orphans-${STAMP}"
MOVED_LOG="${QUARANTINE_DIR}/MOVED.txt"
SKIPPED_LOG="${QUARANTINE_DIR}/SKIPPED.txt"

TARGETS=(
  "app/Views/cadastroAtracao.php"
  "app/Views/calendar.php"
  "app/Views/main-old.php"
  "app/Views/rider.php"
  "app/Views/updateCadastroOperacional.php"
  "app/Views/welcome_message.php"
  "components"
  "public/assets/documents/LPIC-1.pdf"
  "public/assets/images/Captura de tela de 2024-11-20 09-55-35.png"
  "public/assets/images/avatar.jpg"
  "public/assets/images/img.jpg"
  "public/assets/img/apple-touch-icon.png"
  "public/assets/img/coding.jpg"
  "public/assets/img/favicon.png"
  "public/assets/img/hero.jpg"
  "public/assets/img/hero.webp"
  "public/assets/img/hero2.jpg"
  "public/assets/img/hero3.jpg"
  "public/assets/img/logo2.png"
  "public/assets/img/my-about-img.png"
  "public/assets/img/my-profile-img.jpg"
  "public/assets/img/my-profile-img.png"
  "public/assets/img/portfolio/app-1.jpg"
  "public/assets/img/portfolio/app-2.jpg"
  "public/assets/img/portfolio/app-3.jpg"
  "public/assets/img/portfolio/books-1.jpg"
  "public/assets/img/portfolio/books-2.jpg"
  "public/assets/img/portfolio/books-3.jpg"
  "public/assets/img/portfolio/branding-1.jpg"
  "public/assets/img/portfolio/branding-2.jpg"
  "public/assets/img/portfolio/branding-3.jpg"
  "public/assets/img/portfolio/empreendedorismo.png"
  "public/assets/img/portfolio/product-1.jpg"
  "public/assets/img/portfolio/product-2.jpg"
  "public/assets/img/portfolio/product-3.jpg"
  "public/assets/img/rafaelsoje_avatar.webp"
  "public/assets/img/rafaelsoje_sobre.webp"
  "public/assets/img/services.jpg"
  "public/assets/img/testimonials/testimonials-1.jpg"
  "public/assets/img/testimonials/testimonials-2.jpg"
  "public/assets/img/testimonials/testimonials-3.jpg"
  "public/assets/img/testimonials/testimonials-4.jpg"
  "public/assets/img/testimonials/testimonials-5.jpg"
)

mkdir -p "$QUARANTINE_DIR"
: > "$MOVED_LOG"
: > "$SKIPPED_LOG"

move_one() {
  local src="$1"
  local dst="$QUARANTINE_DIR/$src"
  local dst_dir

  if [[ ! -e "$src" ]]; then
    echo "$src (nao encontrado)" | tee -a "$SKIPPED_LOG" >/dev/null
    return
  fi

  if [[ "$MODE" == "--dry-run" ]]; then
    echo "$src"
    return
  fi

  dst_dir="$(dirname "$dst")"
  mkdir -p "$dst_dir"
  mv "$src" "$dst"
  echo "$src -> $dst" >> "$MOVED_LOG"
}

if [[ "$MODE" == "--dry-run" ]]; then
  echo "DRY RUN: arquivos/diretorios que seriam movidos para $QUARANTINE_DIR"
fi

for target in "${TARGETS[@]}"; do
  move_one "$target"
done

if [[ "$MODE" == "--apply" ]]; then
  echo "Quarentena criada em: $QUARANTINE_DIR"
  echo "Movidos: $(wc -l < "$MOVED_LOG")"
  echo "Ignorados: $(wc -l < "$SKIPPED_LOG")"
fi
