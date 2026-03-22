#!/usr/bin/env bash
set -euo pipefail
# File: bin/mk-dtk-bin.sh
# ──────────────────────────────────────────────────────────────────────────────
# Builds standalone DTK binaries for all platforms.
#
# Steps:
# 1. Prepare app for prod environment:
#    no dev deps, autoloading optimisation (classmap authoritative),
#    making .env.local.php from .env, no debug symfony cache, etc
# 2. Compile the PHAR with Box
# 3. Concatenate micro.sfx + dtk.phar into a self-contained binary, per platform
#
# Builds: build/dtk.phar
#         build/dtk-linux-x86_64
#         build/dtk-linux-aarch64
#         build/dtk-macos-x86_64
#         build/dtk-macos-aarch64
#         build/dtk-windows-x86_64.exe
#         build/checksums.txt
#
# Note 1: The micro SAPI is a special minimal PHP binary
# (compiled by static-php-cli as micro-{platform}.sfx),
# that reads the binary data appended to it and executes it as a PHAR.
#
# Note 2: the micro-{platform}.sfx that are downloaded in /usr/local/lib
# in the Docker container only has the standard PHP extensions,
# which is fine for DTK.
# If any other extensions become needed, then we'd need to compile our own micro.sfx.
#
# Note 3: No PHP-Scoper. DTK isn't meant to be installed as a composer package in
# a PHP project, so we don't need to prefix namespaces (e.g. Psr\Log\LoggerInterface
# to DtkScoper\Psr\Log\LoggerInterface) to avoid conflicts.
#
# Usage:
#
# ```shell
# bin/mk-dtk-bin.sh
# ```
# ──────────────────────────────────────────────────────────────────────────────

# Restore dev dependencies once finished
trap 'composer install --optimize-autoloader --quiet' EXIT

echo ''
echo '  // Installing prod dependencies...'
composer install --no-dev --classmap-authoritative --quiet

echo '  // Compiling environment variables...'
php bin/mk-dtk-bin/dump-env-prod.php

echo '  // Warming up Symfony cache...'
APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup --quiet

echo '  // Building PHAR...'
mkdir -p build
php -d phar.readonly=0 /usr/local/bin/box compile

echo '  // Assembling binaries...'
for _PLATFORM in linux-x86_64 linux-aarch64 macos-x86_64 macos-aarch64 windows-x86_64; do
    # Windows binary has `.exe` extension, and should not be chmoded
    case "${_PLATFORM}" in
        windows-*) _EXT='.exe' ; _CHMOD=false ;;
        *)         _EXT=''     ; _CHMOD=true  ;;
    esac

    cat "/usr/local/lib/micro-${_PLATFORM}.sfx" build/dtk.phar > "build/dtk-${_PLATFORM}${_EXT}"
    ${_CHMOD} && chmod +x "build/dtk-${_PLATFORM}${_EXT}"

    echo "       build/dtk-${_PLATFORM}${_EXT}"
done

echo '  // Generating checksums...'
sha256sum \
    build/dtk-linux-x86_64 \
    build/dtk-linux-aarch64 \
    build/dtk-macos-x86_64 \
    build/dtk-macos-aarch64 \
    build/dtk-windows-x86_64.exe \
    > build/checksums.txt
echo '       build/checksums.txt'

echo ''
echo '  [OK] Binaries built'
echo ''
