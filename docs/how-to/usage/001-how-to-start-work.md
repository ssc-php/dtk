# How to start work on a ticket

Start work on a ticket by running `work:start`,
with a templated branch name and a ticket ID:

```console
dtk work:start --new-branch='{ticket_id}/feat/cunning-plan' --ticket-id=PRJ-4423
```

This will:

1. create a new branch
2. switch to it

DTK can replace placeholders in the branch name:

* `{ticket_id}`: with the value of `--ticket-id`

If you omit `--new-branch`, DTK will prompt for it interactively.

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
