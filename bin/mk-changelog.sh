#!/usr/bin/env bash
set -euo pipefail
# File: bin/mk-changelog.sh
# ──────────────────────────────────────────────────────────────────────────────
# Generates a CHANGELOG.md entry from git log (conventional commits),
# following the Keep a Changelog (https://keepachangelog.com/en/1.1.0/) format.
#
# Conventional commits are mapped to Keep a Changelog sections:
# * feat (non-breaking): Added
# * perf:                Changed
# * feat! / <any>!:      Removed (BC breaks = removals by convention)
# * fix:                 Fixed
# * fix(security):       Security
# * chore / refactor / test / docs: (ignored: maintenance, no user-facing change)
#
# Example output in CHANGELOG.md:
#
# ```markdown
# ## [4.2.3] - 2026-03-24
#
# ### Added
# - add export to CSV
#
# ### Fixed
# - handle empty CSV export gracefully
# ```
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
# bin/mk-changelog.sh
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

if ! grep -q '^## \[Unreleased\]$' CHANGELOG.md; then
    echo "  [KO] CHANGELOG.md is missing a '## [Unreleased]' section" >&2
    exit 1
fi

if grep -q "^## \[${_VERSION_RE}\]" CHANGELOG.md; then
    echo "  [KO] CHANGELOG.md already has an entry for ${_VERSION}" >&2
    exit 1
fi

# ──────────────────────────────────────────────────────────────────────────────
# Build CHANGELOG entry:
#
# ```markdown
# ## [${_VERSION}] - $_DATE
#
# ### Added
# - ${_ADDED}
#
# ### Changed
# - ${_CHANGED}
#
# ### Removed
# - ${_REMOVED}
#
# ### Fixed
# - ${_FIXED}
#
# ### Security
# - ${_SECURITY}
# ```
# ──────────────────────────────────────────────────────────────────────────────

_DATE=$(date -u +%Y-%m-%d)
_LAST_TAG=$(git describe --tags --abbrev=0 --match 'v[0-9]*.[0-9]*.[0-9]*' 2>/dev/null || echo "")
_RANGE="${_LAST_TAG:+${_LAST_TAG}..}HEAD"
_GIT_LOG=$(git log "${_RANGE}" --format="%s")

# Title: [Version] - Date
_ENTRY="## [${_VERSION}] - ${_DATE}"

# Added: feat ; without `!` breaking marker
_ADDED=$(echo "${_GIT_LOG}" \
    | grep -E "^feat(\([^)]+\))?:" \
    | sed -E "s/^[a-z]+(\([^)]+\))?: */- /" \
    || true)

# Changed: perf ; without `!` breaking marker
_CHANGED=$(echo "${_GIT_LOG}" \
    | grep -E "^perf(\([^)]+\))?:" \
    | sed -E "s/^[a-z]+(\([^)]+\))?: */- /" \
    || true)

# Removed: any type with `!` breaking marker
_REMOVED=$(echo "${_GIT_LOG}" \
    | grep -E "^[a-z]+(\([^)]+\))?!:" \
    | sed -E "s/^[a-z]+(\([^)]+\))?!: */- /" \
    || true)

# Fixed: fix ; without security scope
_FIXED=$(echo "${_GIT_LOG}" \
    | grep -E "^fix(\([^)]+\))?:" \
    | grep -Ev "^fix\(security\):" \
    | sed -E "s/^[a-z]+(\([^)]+\))?: */- /" \
    || true)

# Security: fix(security)
_SECURITY=$(echo "${_GIT_LOG}" \
    | grep -E "^fix\(security\):" \
    | sed -E "s/^[a-z]+(\([^)]+\))?: */- /" \
    || true)

_append_section() {
    if [[ -n "$2" ]]; then
        _ENTRY="${_ENTRY}"$'\n\n'"### $1"$'\n'"${2%$'\n'}"
    fi
}

_append_section "Added"    "${_ADDED}"
_append_section "Changed"  "${_CHANGED}"
_append_section "Removed"  "${_REMOVED}"
_append_section "Fixed"    "${_FIXED}"
_append_section "Security" "${_SECURITY}"

if [[ "${_ENTRY}" == "## [${_VERSION}] - ${_DATE}" ]]; then
    echo "  [WARN] No conventional commits found in range ${_RANGE}, changelog entry will be empty" >&2
fi

# ──────────────────────────────────────────────────────────────────────────────
# Update CHANGELOG.md
# ──────────────────────────────────────────────────────────────────────────────

_REPO_URL=$(gh repo view --json url -q '.url')
_UNRELEASED_LINK="[Unreleased]: ${_REPO_URL}/compare/v${_VERSION}...HEAD"
if [[ -n "${_LAST_TAG}" ]]; then
    _VERSION_LINK="[${_VERSION}]: ${_REPO_URL}/compare/${_LAST_TAG}...v${_VERSION}"
else
    _VERSION_LINK="[${_VERSION}]: ${_REPO_URL}/releases/tag/v${_VERSION}"
fi

echo ''
echo '  // Updating CHANGELOG.md...'
trap 'rm -f CHANGELOG.md.tmp' EXIT

_ENTRY="${_ENTRY}" \
_UNRELEASED_LINK="${_UNRELEASED_LINK}" \
_VERSION_LINK="${_VERSION_LINK}" \
awk '
    /^## \[Unreleased\]$/ {
        print
        print ""
        print ENVIRON["_ENTRY"]
        next
    }
    /^\[Unreleased\]:/ {
        print ENVIRON["_UNRELEASED_LINK"]
        print ENVIRON["_VERSION_LINK"]
        next
    }
    { print }
' CHANGELOG.md > CHANGELOG.md.tmp
mv CHANGELOG.md.tmp CHANGELOG.md

echo ''
echo "  [OK]   Updated CHANGELOG.md for ${_VERSION}"
echo "  [NEXT] Review and tweak CHANGELOG.md, then run bin/mk-release.sh"
echo ''
