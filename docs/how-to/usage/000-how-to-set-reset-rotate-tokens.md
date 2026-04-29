# How to set, reset and rotate tokens

DTK needs tokens to be able to access services (GitHub, YouTrack, etc).

Here's how to manage them.

## Save a token

Run the command to set, reset, or rotate a token (DTK always overwrites the existing entry),
passing the token via the `DTK_TOKEN` env var:

```console
DTK_TOKEN=<your-token> dtk tokens:save --service=youtrack
```

To be prompted for the token with hidden input instead, pass `--interactive`:

```console
dtk tokens:save --service=youtrack --interactive
```

The hidden-input path is safer because the token never touches the command line,
so it does not appear in shell history or the process list.

> **Warning:** inline env vars are recorded in shell history and visible to
> other processes for the duration of the command.

> **Tip:** when generating tokens, set the shortest expiry the service allows.
> GitHub Personal Access Tokens support fine-grained expiry (e.g. 7 or 30 days).
> YouTrack permanent tokens should be rotated manually on a regular cadence.
