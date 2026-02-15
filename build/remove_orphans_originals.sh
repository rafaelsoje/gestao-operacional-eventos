#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

MODE="${1:---dry-run}"
if [[ "$MODE" != "--dry-run" && "$MODE" != "--apply" ]]; then
  echo "Uso: $0 [--dry-run|--apply]"
  exit 1
fi

TARGETS=(
  "app/Views/cadastroAtracao.php"
  "app/Views/calendar.php"
  "app/Views/main-old.php"
  "app/Views/rider.php"
  "app/Views/updateCadastroOperacional.php"
  "app/Views/welcome_message.php"
  "app/Views/dashboard.php"
  "app/Views/proposta.php"
  "app/Views/cadastroProposta.php"
  "app/Views/updateProposta.php"
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
  "public/media/logo--.png"
  "public/media/Captura de imagem_20241002_105430.png"
  "public/media/3dd8b9abef8f3ad2913693cb26aacdc2ca9eec46.webp"
  "public/media/71cfd3a4578693a03eda06d1cb81743e71aadd2f.webp"
  "public/media/737fb7ab6cc057bffd954fcc0de98bf0cb8e99ff.webp"
  "public/media/8a5fe742de7db40ca1cfc52ded9dd742580386f9.webp"
  "public/media/a41e98c62345191b54755e1604519023dd2bf805.webp"
  "public/media/aadebbb4890b51c376b93e8bdfae1e1322e79348.webp"
  "public/media/bbd952df4faa77338f15f2b62a00218d04f2a0da.webp"
  "public/media/f75632a0d2b74358d46d7dc148d80a0443f91cd1.webp"
  "public/media/fc482b5cd67c2ebbc3d8ef94b7438ad5e52c1755.webp"
  "public/media/fea25805d66ac547836a2721e667250454d9e36b.webp"
  "public/media/logo-black.png"
)

is_safe_path() {
  local p="$1"
  [[ -n "$p" ]] || return 1
  [[ "$p" != /* ]] || return 1
  [[ "$p" != *".."* ]] || return 1
  [[ "$p" != "." ]] || return 1
  [[ "$p" != ".." ]] || return 1
  return 0
}

echo "Modo: $MODE"
echo "Alvos: ${#TARGETS[@]}"

removed=0
missing=0

for target in "${TARGETS[@]}"; do
  if ! is_safe_path "$target"; then
    echo "[SKIP] caminho inseguro: $target"
    continue
  fi

  if [[ "$MODE" == "--dry-run" ]]; then
    if [[ -e "$target" ]]; then
      echo "[DRY-RUN] removeria: $target"
    else
      echo "[DRY-RUN] ausente: $target"
    fi
    continue
  fi

  if [[ -e "$target" ]]; then
    rm -rf -- "$target"
    echo "[OK] removido: $target"
    removed=$((removed + 1))
  else
    echo "[SKIP] ausente: $target"
    missing=$((missing + 1))
  fi
done

if [[ "$MODE" == "--apply" ]]; then
  echo "Resumo: removidos=$removed ausentes=$missing"
fi
