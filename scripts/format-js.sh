#!/bin/sh
# Formats frontend files with Shoutrrr's oxfmt inside a throwaway container and copies the
# results to /out/fmt (the caller copies them back), so node_modules never lands on Windows.
# Usage: format-js.sh <path relative to app/> ...
set -eu
mkdir -p /work && cd /work
tar -C /src --exclude=node_modules --exclude=vendor --exclude=public/build -cf - . | tar -xf -
bun install --frozen-lockfile > /dev/null 2>&1 || bun install > /dev/null 2>&1
bun x oxfmt "$@"
rm -rf /out/fmt && mkdir -p /out/fmt
for f in "$@"; do
  mkdir -p "/out/fmt/$(dirname "$f")"
  cp "$f" "/out/fmt/$f"
done
bun x oxfmt --check resources/ && echo "oxfmt: all files formatted"
