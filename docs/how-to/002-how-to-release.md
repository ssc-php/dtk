# How to release

## Prerequisites

* [Docker](https://docs.docker.com/get-docker/): for `make app-prep-release` (builds binaries inside the container)
* [gh CLI](https://cli.github.com): for `make app-release` (creates the GitHub release)

## 1. Prepare the release

```console
make app-prep-release version=X.Y.Z
```

This will:

1. Bump the version in `src/Infrastructure/Symfony/Version.php` (`dtk --version`)
2. Build the binaries for all platforms
3. Generate the `CHANGELOG.md` entry from git log since the last tag

> ⚠️ **Review and tweak `CHANGELOG.md` before moving on**.

## 2. Create the release

```console
make app-release
```

This will:

1. Verify the working tree is clean (only `CHANGELOG.md` and `Version.php` may be modified)
2. Commit `CHANGELOG.md` and `Version.php`
3. Create an annotated git tag `vX.Y.Z`
4. Push the commit and tag
5. Create a GitHub release with the binaries and `checksums.txt` attached
6. Update the Homebrew formula in `ssc-php/homebrew-dtk` and push it

## Bump PHP version

To check that all resources (Docker image, micro SAPI binaries) are available for a new PHP version:

```console
bin/bump/php.sh --version     # display the currently used PHP version
bin/bump/php.sh 8.5.5         # check availability of PHP 8.5.5
bin/bump/php.sh 8.5.5 --apply # check availability and bump Dockerfile to 8.5.5
```

Once bumped, rebuild the Docker image to apply the change:

```console
make app-init
```
