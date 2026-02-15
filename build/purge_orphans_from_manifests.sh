#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

MODE="--dry-run"
declare -a MANIFESTS=()

while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run|--apply)
      MODE="$1"
      shift
      ;;
    --manifest)
      if [[ $# -lt 2 ]]; then
        echo "Erro: --manifest precisa de um caminho." >&2
        exit 1
      fi
      MANIFESTS+=("$2")
      shift 2
      ;;
    *)
      echo "Uso: $0 [--dry-run|--apply] [--manifest <arquivo_MOVED.txt>]..." >&2
      exit 1
      ;;
  esac
done

if [[ "${#MANIFESTS[@]}" -eq 0 ]]; then
  while IFS= read -r f; do
    MANIFESTS+=("$f")
  done < <(find writable/quarantine -type f -name MOVED.txt 2>/dev/null | sort)
fi

if [[ "${#MANIFESTS[@]}" -eq 0 ]]; then
  echo "Nenhum MOVED.txt encontrado em writable/quarantine."
  exit 0
fi

is_safe_path() {
  local p="$1"
  [[ -n "$p" ]] || return 1
  [[ "$p" != /* ]] || return 1
  [[ "$p" != *".."* ]] || return 1
  return 0
}

declare -a TARGETS=()

for mf in "${MANIFESTS[@]}"; do
  if [[ ! -f "$mf" ]]; then
    echo "Manifesto não encontrado: $mf" >&2
    continue
  fi

  while IFS= read -r line; do
    [[ -n "$line" ]] || continue
    src="${line%% -> *}"
    if is_safe_path "$src"; then
      TARGETS+=("$src")
    else
      echo "Ignorando caminho inseguro no manifesto $mf: $src" >&2
    fi
  done < "$mf"
done

if [[ "${#TARGETS[@]}" -eq 0 ]]; then
  echo "Nenhum alvo válido encontrado nos manifestos."
  exit 0
fi

mapfile -t UNIQUE_TARGETS < <(printf '%s\n' "${TARGETS[@]}" | awk '!seen[$0]++')

echo "Modo: $MODE"
echo "Manifestos: ${#MANIFESTS[@]}"
echo "Alvos únicos: ${#UNIQUE_TARGETS[@]}"

removed=0
missing=0

for p in "${UNIQUE_TARGETS[@]}"; do
  if [[ "$MODE" == "--dry-run" ]]; then
    if [[ -e "$p" ]]; then
      echo "[DRY-RUN] removeria: $p"
    else
      echo "[DRY-RUN] ausente: $p"
    fi
    continue
  fi

  if [[ -e "$p" ]]; then
    rm -rf -- "$p"
    echo "[OK] removido: $p"
    removed=$((removed + 1))
  else
    echo "[SKIP] ausente: $p"
    missing=$((missing + 1))
  fi
done

if [[ "$MODE" == "--apply" ]]; then
  echo "Resumo: removidos=$removed ausentes=$missing"
fi
