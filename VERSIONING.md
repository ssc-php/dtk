# VERSIONING

This file explains the versioning model of this project.

## Versioning

The versioning is inspired by [Semantic Versioning](http://semver.org/):

> Given a version number MAJOR.MINOR.PATCH, increment the:
>
> 1. MAJOR version when you make incompatible API changes
> 2. MINOR version when you add functionality in a backwards-compatible manner
> 3. PATCH version when you make backwards-compatible bug fixes

### Public API

The public API of this project is the CLI: the commands, options, and behaviour
documented under `docs/how-to/usage/`. Anything documented there is considered
stable. A BC break is any change to a documented command name, option, or
behaviour, visible as a change to those doc files in the diff.
