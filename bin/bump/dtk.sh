#!/usr/bin/env bash
set -euo pipefail
# File: bin/bump/dtk.sh
# ──────────────────────────────────────────────────────────────────────────────
# Bumps the DTK version constant in src/Infrastructure/Symfony/Version.php.
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
# bin/bump/dtk.sh --version     # display current version
# bin/bump/dtk.sh 4.2.3         # bump to 4.2.3
# ```
# ──────────────────────────────────────────────────────────────────────────────

_BIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]:-$0}")" && pwd)"
cd "${_BIN_DIR}/../.."

_VERSION_FILE='src/Infrastructure/Symfony/Version.php'

_ARG="${1:-}"

if [[ "${_ARG}" == "--version" ]]; then
    sed -n "s/.*VERSION = '\([^']*\)'.*/\1/p" "${_VERSION_FILE}"
    exit 0
fi

_VERSION="${_ARG}"
if [[ -z "${_VERSION}" ]]; then
    echo "Usage: bin/bump/dtk.sh <version>" >&2
    echo "       bin/bump/dtk.sh --version" >&2
    exit 1
fi
if [[ ! "${_VERSION}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Error: version must be X.Y.Z (e.g. 4.2.3)" >&2
    exit 1
fi

_CURRENT=$(sed -n "s/.*VERSION = '\([^']*\)'.*/\1/p" "${_VERSION_FILE}")

if [[ "${_CURRENT}" == "${_VERSION}" ]]; then
    echo "  [OK] Version is already ${_VERSION}"
    echo ''
    exit 0
fi

echo ''
echo "  // Bumping DTK version ${_CURRENT} -> ${_VERSION}..."

_TMP=$(mktemp)
trap 'rm -f "${_TMP}"' EXIT
sed "s/VERSION = '[^']*'/VERSION = '${_VERSION}'/" "${_VERSION_FILE}" > "${_TMP}"
mv "${_TMP}" "${_VERSION_FILE}"

echo ''
echo "  [OK]   DTK version bumped to ${_VERSION}"
echo "  [NEXT] Run mk-dtk-bin.sh"
echo ''
