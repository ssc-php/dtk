# Token storage

DTK stores tokens in the first available backend, checked in this order:

| Priority | Backend              | Condition                                  |
|----------|----------------------|--------------------------------------------|
| 1        | Linux Secret Service | Linux only, `secret-tool` installed        |
| 2        | macOS Keychain       | macOS only                                 |
| 3        | Plain file           | Always available (fallback)                |

## Linux Secret Service

Requires `secret-tool` to be installed and a Secret Service daemon running.

> **Note**: Most GNOME and KDE distributions (Ubuntu, Fedora, etc.) include this by default.
> But headless or minimal installs (servers, WSL, containers) typically don't.

Command used:

```console
echo -n <token> | secret-tool store \
    --label 'dtk:<service>' \
    account dtk \
    service <service> # e.g. github
```

The token is passed via stdin and does not appear in the process list.

## macOS Keychain

Command used:

```console
security add-generic-password \
    -a dtk \
    -s <service> \
    -w <token> \
    -U
```

> **Note:** the token is passed as a command-line argument and is visible to
> local process inspection tools (e.g. `ps`).
> Unfortunately there is no safer input path for the `security` CLI.

## Plain file

Used when no OS keyring is available. Tokens are written to:

```
$DTK_DATA_DIR/tokens.json
```

File format:

```json
{
  "youtrack": "my-secret-token"
}
```

Permissions:

| Path                        | Permissions | Reason                |
|-----------------------------|-------------|-----------------------|
| `$DTK_DATA_DIR/`            | `0700`      | owner access only     |
| `$DTK_DATA_DIR/tokens.json` | `0600`      | owner read/write only |

> **Warning:** tokens are stored as plain text. Anyone with read access to
> the file can extract them. Install an OS keyring to avoid this fallback.
