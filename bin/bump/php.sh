#!/usr/bin/env bash
set -euo pipefail
# File: bin/bump/php.sh
# ──────────────────────────────────────────────────────────────────────────────
# Verifies that all resources required for a PHP version bump are available,
# then updates ARG PHP_VERSION in the Dockerfile.
#
# Checks:
# * Docker Hub: php:{version}-cli-alpine
# * micro.sfx:  linux-x86_64, linux-aarch64, macos-x86_64, macos-aarch64 (tar.gz)
# * micro.sfx:  windows-x86_64 (zip)
#
# Usage:
#
# ```shell
# bin/bump/php.sh --version        # display the currently used PHP version
# bin/bump/php.sh 8.5.5            # check availability of PHP 8.5.5
# bin/bump/php.sh 8.5.5 --apply    # check availability and bump Dockerfile to 8.5.5
# ```
# ──────────────────────────────────────────────────────────────────────────────

_ARG="${1:-}"

if [[ "${_ARG}" == "--version" ]]; then
    grep '^ARG PHP_VERSION=' Dockerfile | cut -d= -f2
    exit 0
fi

_VERSION="${_ARG}"
_APPLY="${2:-}"
if [[ -z "${_VERSION}" ]]; then
    echo "Usage: bin/bump/php.sh <version> [--apply]" >&2
    echo "       bin/bump/php.sh --version" >&2
    echo "Example: bin/bump/php.sh 8.5.5" >&2
    echo "         bin/bump/php.sh 8.5.5 --apply" >&2
    exit 1
fi

_FAIL=false

_check() {
    local _LABEL="$1"
    local _URL="$2"

    local _STATUS
    _STATUS=$(curl --silent --output /dev/null --write-out '%{http_code}' --location "${_URL}")

    if [[ "${_STATUS}" == "200" ]]; then
        echo "  [OK] ${_LABEL}"
    else
        echo "  [KO] ${_LABEL} (HTTP ${_STATUS})"
        _FAIL=true
    fi
}

echo ''
echo "  // Checking availability of PHP ${_VERSION}..."
echo ''

_check \
    "Docker Hub: php:${_VERSION}-cli-alpine" \
    "https://hub.docker.com/v2/repositories/library/php/tags/${_VERSION}-cli-alpine"

for _PLATFORM in linux-x86_64 linux-aarch64 macos-x86_64 macos-aarch64; do
    _check \
        "micro.sfx: ${_PLATFORM}" \
        "https://dl.static-php.dev/static-php-cli/common/php-${_VERSION}-micro-${_PLATFORM}.tar.gz"
done

_check \
    "micro.sfx: windows-x86_64" \
    "https://dl.static-php.dev/static-php-cli/windows/spc-min/php-${_VERSION}-micro-win.zip"

echo ''

if [[ "${_FAIL}" == "true" ]]; then
    echo "  [KO] Not all resources are available for PHP ${_VERSION}"
    echo ''
    exit 1
fi

if [[ "${_APPLY}" == "--apply" ]]; then
    _TMP=$(mktemp)
    sed "s/^ARG PHP_VERSION=.*/ARG PHP_VERSION=${_VERSION}/" Dockerfile > "${_TMP}"
    mv "${_TMP}" Dockerfile
    echo "  [OK] Dockerfile bumped to PHP ${_VERSION}"
else
    echo "  [OK] PHP ${_VERSION} is available (run with --apply to bump)"
fi

echo ''
