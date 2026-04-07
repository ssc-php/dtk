# File token storage

Fallback strategy used when no OS keyring is available, stores tokens in a JSON file on the filesystem.

> **Warning:** this is not safe and should be avoided.
> Tokens are stored as plain text, anyone with read access to the file can extract them.

## Location

The config directory is configured via the `DTK_CONFIG_DIR` environment variable.

## File structure

```
$DTK_CONFIG_DIR/
└── tokens.json   (0600)
```

`tokens.json` maps service names to their tokens:

```json
{
    "youtrack": "my-secret-token"
}
```

## Permissions

| Path                          | Permissions | Reason                |
|-------------------------------|-------------|-----------------------|
| `$DTK_CONFIG_DIR/`            | `0700`      | owner access only     |
| `$DTK_CONFIG_DIR/tokens.json` | `0600`      | owner read/write only |
