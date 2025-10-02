#!/usr/bin/env bash
# Simple wrapper to run a local LLM binary (e.g., llama.cpp's main) with a prompt
# Usage example (as LLM_COMMAND in .env):
# ./scripts/run_local_llm.sh --bin /opt/llama/main --model /models/7B/ggml-model.bin
# The script reads the prompt from stdin and forwards it to the binary. It prints
# the raw stdout of the binary (we expect JSON somewhere in the output). If the
# binary is not available the script prints a small JSON placeholder.

set -e

BIN=""
MODEL=""
ARGS=()

while [[ "$#" -gt 0 ]]; do
  case "$1" in
    --bin) BIN="$2"; shift 2;;
    --model) MODEL="$2"; shift 2;;
    --) shift; break;;
    *) ARGS+=("$1"); shift;;
  esac
done

# read entire stdin to PROMPT
PROMPT="$(cat -)"

if [[ -n "$BIN" && -x "$BIN" ]]; then
  # Try to call the binary. Many llama.cpp wrappers accept -m and -p or -t flags.
  # This is a best-effort; you may need to adapt flags for your runner.
  if [[ -n "$MODEL" ]]; then
    "$BIN" -m "$MODEL" -p "$PROMPT" "${ARGS[@]}"
  else
    "$BIN" -p "$PROMPT" "${ARGS[@]}"
  fi
  exit 0
fi

# Fallback: output a minimal JSON object with nulls so the controller can continue
cat <<'JSON'
{ "name": null, "number": null }
JSON
