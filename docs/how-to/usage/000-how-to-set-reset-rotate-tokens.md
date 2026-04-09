# How to set, reset and rotate tokens

DTK needs tokens to be able to access services (GitHub, YouTrack, etc).

Here's how to manage them.

## Save a token

Run the command to set, reset, or rotate a token (DTK always overwrites the existing entry):

```console
dtk tokens:save --service=youtrack
```

DTK will prompt for the token with hidden input. The token never touches the
command line or the environment, so it does not appear in shell history or
the process list.

Alternatively (less safe), pass it via the `DTK_TOKEN` env var (for CI pipelines and
non-interactive contexts):

```console
DTK_TOKEN=<your-token> dtk tokens:save --service=youtrack
```

> **Warning:** inline env vars are recorded in shell history and visible to
> other processes for the duration of the command.

> **Tip:** when generating tokens, set the shortest expiry the service allows.
> GitHub Personal Access Tokens support fine-grained expiry (e.g. 7 or 30 days).
> YouTrack permanent tokens should be rotated manually on a regular cadence.
