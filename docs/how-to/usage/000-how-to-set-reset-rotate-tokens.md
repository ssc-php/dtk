# How to set, reset and rotate tokens

DTK needs tokens to be able to access services (GitHub, YouTrack, etc).

Here's how to manage them.

- [Save a token](#save-a-token)
  - [Jira](#jira)

## Save a token

Run the command to set, reset, or rotate a token (DTK always overwrites the existing entry),
passing `--interactive` to be prompted with hidden input:

```console
dtk tokens:save --service=youtrack --interactive
```

The interactive command is safer because the token never touches the command line,
so it does not appear in shell history or the process list.

For scripting or automation where no interactive terminal is available,
pass the token via the `DTK_TOKEN` env var instead:

```console
DTK_TOKEN=<your-token> dtk tokens:save --service=youtrack
```

> **Warning:** inline env vars are recorded in shell history and visible to
> other processes for the duration of the command.

> **Tip:** when generating tokens, set the shortest expiry the service allows (e.g. 7 or 30 days).
> Permanent tokens (e.g. YouTrack) should be rotated manually on a regular cadence.

### Jira

Jira requires a token in the `<email>:<api-token>` format, where `<email>` is
the email address of your Atlassian account.

To generate a Jira API token:

1. Go to <https://id.atlassian.com/manage-profile/security/api-tokens>
2. Click **Create API token**
3. Give it a label (e.g. `dtk-jira-2605`), and expiry date (e.g. `01/06/2026`) and click **Create**
4. Copy the token before closing the dialog (it is shown only once)

Then save it:

```console
dtk tokens:save --service=jira --interactive
```

To revoke a Jira API token:

1. Go to <https://id.atlassian.com/manage-profile/security/api-tokens>
2. Click **Revoke** on the token's row
