# How to start work on a ticket

Start work on a ticket by running `work:start`,
with a templated branch name and a ticket ID (or ticket URL):

```console
dtk work:start --new-branch='{ticket_id}/feat/cunning-plan' --ticket-id=PRJ-4423
```

This will:

1. create a new branch
2. switch to it

DTK can replace placeholders in the branch name:

* `{ticket_id}`: with the value of `--ticket-id`

If you omit `--new-branch`, DTK will exit with an error.
Pass `--interactive` to be prompted for it instead.

## Use a ticket URL to auto-build the branch name and sync the board

Pass a full ticket URL instead of a ticket ID to let DTK fetch the ticket
metadata and move it to "In Progress" on the board:

```console
dtk work:start --new-branch='{ticket_id}/{type}/{title}' --ticket-url=https://company.atlassian.net/browse/PRJ-4423
```

This will:

1. fetch the ticket from the Kanban board (Jira)
2. build the branch name from the template
3. create a new branch and switch to it
4. move the ticket to "In Progress" on the board

When `--ticket-url` is provided, three placeholders are available:

* `{ticket_id}`: the ticket identifier (e.g. `PRJ-4423`)
* `{type}`: the ticket type, slugified (e.g. `story`, `bug`)
* `{title}`: the ticket title, slugified (e.g. `cunning-plan`)

## Specify a starting point

By default the new branch is created from `origin/main`.

You can specify another value using `--starting-point`:

```console
dtk work:start --new-branch='{ticket_id}/feat/cunning-plan' --ticket-id=PRJ-4423 --starting-point=origin/master
```

## Keep uncommitted changes

By default uncommitted changes are carried over to the new branch,
if they don't conflict with it. But if they do, the command fails.

Use `--autostash` to automatically stash any uncommitted changes before the switch,
and then automatically unstash them on the new branch:

```console
dtk work:start --ticket-id=PRJ-4423 --new-branch='{ticket_id}/feat/cunning-plan' --autostash
```

Any conflicted changes will be left on the new branch,
with markers for manual resolution (just like what `git stash pop` does).
