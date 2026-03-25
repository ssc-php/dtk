#!/usr/bin/env bash
set -euo pipefail
# File: bin/mk-release.sh
# ──────────────────────────────────────────────────────────────────────────────
# Creates a DTK release:
# 1. Verifies the working tree is clean (only CHANGELOG.md and Version.php may be modified)
# 2. Commits CHANGELOG.md and Version.php
# 3. Creates an annotated git tag
# 4. Pushes the tag and commit
# 5. Creates a GitHub release, attaching the platform binaries and checksums
#
# Running order:
# 1. bump/dtk.sh
# 2. mk-dtk-bin.sh
# 3. mk-changelog.sh
# 4. review and tweak CHANGELOG.md
# 5. mk-release.sh
#
# Usage:
#
# ```shell
# bin/mk-release.sh
# ```
# ──────────────────────────────────────────────────────────────────────────────

_BIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]:-$0}")" && pwd)"
cd "${_BIN_DIR}/.."

if ! command -v gh &>/dev/null; then
    echo "Error: gh CLI is required (https://cli.github.com)" >&2
    exit 1
fi

_VERSION=$(sed -n "s/.*VERSION = '\([^']*\)'.*/\1/p" src/Infrastructure/Symfony/Version.php)
_VERSION_RE="${_VERSION//./\\.}"

# The actual tag is prefixed with `v`
_TAG="v${_VERSION}"

if git tag -l "${_TAG}" | grep -q .; then
    echo "Error: tag ${_TAG} already exists" >&2
    exit 1
fi

_CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [[ "${_CURRENT_BRANCH}" != "main" ]]; then
    echo "Error: releases must be made from 'main', currently on '${_CURRENT_BRANCH}'" >&2
    exit 1
fi

if ! grep -q "^## \[${_VERSION_RE}\]" CHANGELOG.md; then
    echo "Error: no entry for ${_VERSION} in CHANGELOG.md, run bin/mk-changelog.sh first" >&2
    exit 1
fi

for _BIN in \
    build/dtk-linux-x86_64 \
    build/dtk-linux-aarch64 \
    build/dtk-macos-x86_64 \
    build/dtk-macos-aarch64 \
    build/dtk-windows-x86_64.exe \
    build/checksums.txt
do
    if [[ ! -f "${_BIN}" ]]; then
        echo "Error: missing build artifact: ${_BIN}" >&2
        exit 1
    fi
done

# Extract the **version entry** from CHANGELOG.md for the tag message and release notes
_ENTRY=$(awk "/^## \[${_VERSION_RE}\]/{found=1} found && /^## \[/ && !/^## \[${_VERSION_RE}\]/{exit} found{print}" CHANGELOG.md)

# ──────────────────────────────────────────────────────────────────────────────
# Commit, tag, release
# ──────────────────────────────────────────────────────────────────────────────

_VERSION_FILE='src/Infrastructure/Symfony/Version.php'

if ! git diff --quiet -- ":!CHANGELOG.md" ":!${_VERSION_FILE}" || \
   ! git diff --cached --quiet -- ":!CHANGELOG.md" ":!${_VERSION_FILE}"; then
    echo "Error: working tree is not clean (only CHANGELOG.md and Version.php may be modified)" >&2
    exit 1
fi

echo ''

echo '  // Committing CHANGELOG.md and Version.php...'
git add CHANGELOG.md "${_VERSION_FILE}"
git commit -m "chore: release ${_TAG}"

echo '  // Creating annotated tag...'
git tag -a "${_TAG}" -m "${_ENTRY}"

echo '  // Pushing...'
git push --follow-tags

echo '  // Creating GitHub release...'
_NOTES_FILE=$(mktemp)
trap 'rm -f "${_NOTES_FILE}"' EXIT
printf '%s\n' "${_ENTRY}" > "${_NOTES_FILE}"
gh release create "${_TAG}" \
    --title "${_TAG}" \
    --notes-file "${_NOTES_FILE}" \
    build/dtk-linux-x86_64 \
    build/dtk-linux-aarch64 \
    build/dtk-macos-x86_64 \
    build/dtk-macos-aarch64 \
    build/dtk-windows-x86_64.exe \
    build/checksums.txt

echo ''
echo "  [OK] Released ${_TAG}"
echo ''
