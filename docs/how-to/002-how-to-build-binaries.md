# How to build binaries

Builds standalone DTK binaries for all platforms (Linux, macOS, Windows):

```console
make app-bin
```

This will create in `build/`:

```
build/dtk-linux-x86_64
build/dtk-linux-aarch64
build/dtk-macos-x86_64
build/dtk-macos-aarch64
build/dtk-windows-x86_64.exe
build/checksums.txt
```

Rename the binary for your platform to `dtk` and that's it,
you can now run `dtk` **without having PHP installed** on the machine!

This is done with the following three steps:

1. Prepare app for prod environment:
   no dev deps, autoloading optimisation (classmap authoritative),
   making .env.local.php from .env, no debug symfony cache, etc
2. Compile the app into a `dtk.phar`,
    using [Box](https://box-project.github.io/box/)
3. Concatenate `micro.sfx` + `dtk.phar` into a self-contained binary, per platform
    using [micro SAPI](https://static-php.dev)

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
