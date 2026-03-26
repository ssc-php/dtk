#!/usr/bin/env bash
set -euo pipefail
# File: bin/mk-brew-formula.sh
# ──────────────────────────────────────────────────────────────────────────────
# Updates the Homebrew formula in ../homebrew-dtk with the current version and
# checksums from build/checksums.txt, then commits and pushes to the tap repo.
#
# Requires ../homebrew-dtk to be a clone of ssc-php/homebrew-dtk.
#
# Running order:
# 1. bump/dtk.sh
# 2. mk-dtk-bin.sh
# 3. mk-changelog.sh
# 4. review and tweak CHANGELOG.md
# 5. mk-release.sh  (calls this script automatically)
#
# Usage:
#
# ```shell
# bin/mk-brew-formula.sh
# ```
# ──────────────────────────────────────────────────────────────────────────────

_BIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]:-$0}")" && pwd)"
cd "${_BIN_DIR}/.."

_TAP_DIR="${_BIN_DIR}/../../homebrew-dtk"

if [[ ! -d "${_TAP_DIR}/.git" ]]; then
    echo "Error: ${_TAP_DIR} is not a git repository" >&2
    exit 1
fi

if [[ ! -f build/checksums.txt ]]; then
    echo "Error: missing build/checksums.txt, run bin/mk-dtk-bin.sh first" >&2
    exit 1
fi

_VERSION=$(sed -n "s/.*VERSION = '\([^']*\)'.*/\1/p" src/Infrastructure/Symfony/Version.php)

while read -r _sha _bin; do
    case "${_bin}" in
        *dtk-macos-x86_64)  _SHA_MACOS_X86="${_sha}" ;;
        *dtk-macos-aarch64) _SHA_MACOS_ARM="${_sha}" ;;
        *dtk-linux-x86_64)  _SHA_LINUX_X86="${_sha}" ;;
        *dtk-linux-aarch64) _SHA_LINUX_ARM="${_sha}" ;;
    esac
done < build/checksums.txt

echo ''
echo '  // Updating Homebrew formula...'

# \#{} is Ruby string interpolation; escaped here so bash passes it through literally
cat > "${_TAP_DIR}/Formula/dtk.rb" <<FORMULA
class Dtk < Formula
  desc "Kanban, Git and Deployment, in one coherent flow"
  homepage "https://github.com/ssc-php/dtk"
  license "MIT"
  version "${_VERSION}"

  on_macos do
    on_intel do
      url "https://github.com/ssc-php/dtk/releases/download/v\#{version}/dtk-macos-x86_64"
      sha256 "${_SHA_MACOS_X86}"
    end

    on_arm do
      url "https://github.com/ssc-php/dtk/releases/download/v\#{version}/dtk-macos-aarch64"
      sha256 "${_SHA_MACOS_ARM}"
    end
  end

  on_linux do
    on_intel do
      url "https://github.com/ssc-php/dtk/releases/download/v\#{version}/dtk-linux-x86_64"
      sha256 "${_SHA_LINUX_X86}"
    end

    on_arm do
      url "https://github.com/ssc-php/dtk/releases/download/v\#{version}/dtk-linux-aarch64"
      sha256 "${_SHA_LINUX_ARM}"
    end
  end

  def install
    bin.install Dir["dtk-*"].first => "dtk"
  end

  test do
    assert_match version.to_s, shell_output("\#{bin}/dtk --version")
  end
end
FORMULA

git -C "${_TAP_DIR}" add Formula/dtk.rb
git -C "${_TAP_DIR}" commit -m "chore: release v${_VERSION}"
git -C "${_TAP_DIR}" push

echo ''
echo "  [OK] Updated Homebrew formula for v${_VERSION}"
echo ''
