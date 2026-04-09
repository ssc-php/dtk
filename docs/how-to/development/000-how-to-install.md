# How to install

> Note: this is for local development.

Requirements:

* [Docker](https://www.docker.com/)
* [GNU Make](https://www.gnu.org/software/make/)

Use the Docker container to install and run the application,
with the help of the Makefile:

```console
# Builds the Docker image, starts the Docker service, installs Composer dependencies
make app-init
```

You can now use dtk inside the container:

```console
# Main "help" screen
make dtk

# Individual command
make dtk arg="[command] [arguments/options]"
```

Find further how-to guides in `docs/how-to/development/`.
