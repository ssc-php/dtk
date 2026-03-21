# Devonshire Tea caKe (DTK)

A Devonshire Tea caKe is simple, satisfying, and pairs well with focused work:

> Open ticket → create branch → code → open PR → merge → deploy.

Unfortunately, reality looks more like:

> Open ticket → read the ticket ID → switch to terminal → forget the ID
> → go back to ticket → copy ID → create branch with name `<ticket-id>/<ticket-title>`
> → forget to move the ticket to "In progress" → open PR → forget to link ticket → merge.
> Ticket still says "In Progress" three weeks later.

`dtk` cuts through the repetitive ceremony.
It connects Kanban board, Git workflow, and Deployment into one coherent flow,
improving Developer eXperience so we can get back to the actual baking.

## Features

* [ ] **Create a branch** from a ticket ID or URL:
    * Branch is automatically named from the ticket ID and slugified title
    * Dev is automatically assigned to the ticket
    * Ticket is automatically moved to WIP
* [ ] **Open a PR** based on the current branch:
    * PR title and description are automatically generated from commits and ticket metadata, with the ticket ID and link included
    * Ticket is automatically moved to In Review
* [ ] **Merge a PR**:
    * Local and remote branches are automatically deleted
    * Ticket is automatically moved to Staging
* [ ] **Create a tag**, to deploy:
    * Version tag and message are automatically generated from the commit log
    * GitHub release automatically created
    * A recap is posted to Slack
    * Ticket is automatically moved to Done

All commands are configurable to fit any workflow, with support for:

* [ ] Jira
* [ ] Trello
* [ ] GitHub Projects
* [ ] JetBrains YouTrack

## Getting started

For development purpose, everything is run inside a Docker container,
which can be abstracted using `make` as a task runner:

```console
# First install (Docker build and up)
make app-init

# Run dtk
make dtk

# Run the full QA pipeline (cs, phpstan, rector, phpunit)
make app-qa

# Run tests (PHPUnit)
make phpunit

# Run tests for a specific class, with readable output, in definition order
make phpunit arg='--testdox --order-by=default --filter MyTest'

# Check coding standards (PHP-CS-Fixer)
make cs-check

# Fix coding standards (Swiss Knife PSR-4 alignment + PHP-CS-Fixer)
make cs-fix

# Static analysis (PHPStan)
make phpstan-analyze

# Automated refactoring checks (Rector)
make rector-check

# Fix automated refactorings (Rector)
make rector-fix

# Discover everything you can do
make
```

## Want to know more?

You can see the current and past versions using one of the following:

* the `git tag` command
* the [releases page on Github](https://github.com/ssc/dtk/releases)
* the file listing the [changes between versions](CHANGELOG.md)

And finally some meta documentation:

* [copyright and MIT license](LICENSE)
* [versioning model](VERSIONING.md)
* [contribution instructions](CONTRIBUTING.md)
