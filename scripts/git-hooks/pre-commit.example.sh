#!/usr/bin/env sh
set -eu

echo "[pre-commit] Generating docs/ai-handbook/generated ..."
php artisan -q docs:generate

git add docs/ai-handbook/generated || true
git add docs/ai-handbook/modules/generated || true
