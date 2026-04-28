# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.3.2] - 2026-04-28

### Fixed
- declare symfony/process as a direct dependency

## [0.3.1] - 2026-04-28

### Fixed
- fix boot errors displaying as HTML instead of a clean CLI message

### Changed
- reduce PHAR size by ~27% (2.11MB to 1.55MB) by enabling PHP and JSON compactors in Box

## [0.3.0] - 2026-04-28

### Added
- add `dtk work:start` command, to create and switch to a new branch with a templated name

## [0.2.0] - 2026-04-11

### Added
- add `dtk tokens:save` command, with 3 token storage backends:
  - macOS Keychain (via `security`)
  - Linux Secret Tool (via `secret-tool`)
  - file-based fallback

## [0.1.0] - 2026-03-25

### Added
- add `dtk` app
- add `dtk --version` flag

[Unreleased]: https://github.com/ssc-php/dtk/compare/v0.3.2...HEAD
[0.3.2]: https://github.com/ssc-php/dtk/compare/v0.3.1...v0.3.2
[0.3.1]: https://github.com/ssc-php/dtk/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/ssc-php/dtk/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/ssc-php/dtk/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/ssc-php/dtk/releases/tag/v0.1.0
