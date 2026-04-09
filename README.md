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

### Homebrew install (macOS and Linux)

```console
brew tap ssc-php/dtk
brew install dtk
```

To upgrade:

```console
brew upgrade dtk
```

#### Manual install

Download the binary for your platform from the [releases page](https://github.com/ssc-php/dtk/releases):

| Platform       | Binary                   | Examples                                |
|----------------|--------------------------|-----------------------------------------|
| Linux aarch64  | `dtk-linux-aarch64`      | AWS Graviton, Raspberry Pi, ARM servers |
| macOS aarch64  | `dtk-macos-aarch64`      | Apple Silicon Macs (M1, etc)            |
| macOS x86_64   | `dtk-macos-x86_64`       | Intel Macs (pre-2020)                   |
| Windows x86_64 | `dtk-windows-x86_64.exe` | Most Windows desktops and servers       |

<details>
<summary><strong>🐧 On Linux:</strong></summary>

```console
curl --proto '=https' --tlsv1.2 -fsSL "https://github.com/ssc-php/dtk/releases/latest/download/dtk-linux-x86_64" -o /tmp/dtk

install -m 755 -D /tmp/dtk ~/.local/bin/dtk
```

Verify the checksum:

```console
curl --proto '=https' --tlsv1.2 -fsSL "https://github.com/ssc-php/dtk/releases/latest/download/checksums.txt" \
  | grep "dtk-linux-x86_64" | awk '{print $1 "  /tmp/dtk"}' | sha256sum --check
```

> On ARM (e.g. AWS Graviton, Raspberry Pi, etc), replace `dtk-linux-x86_64` with `dtk-linux-aarch64`.

</details>

<details>
<summary><strong>🍎 On macOS:</strong></summary>

```console
curl --proto '=https' --tlsv1.2 -fsSL "https://github.com/ssc-php/dtk/releases/latest/download/dtk-macos-aarch64" -o /tmp/dtk

install -m 755 /tmp/dtk ~/.local/bin/dtk
```

Verify the checksum:

```console
curl --proto '=https' --tlsv1.2 -fsSL "https://github.com/ssc-php/dtk/releases/latest/download/checksums.txt" \
  | grep "dtk-macos-aarch64" | awk '{print $1 "  /tmp/dtk"}' | shasum -a 256 --check
```

> On Intel Macs (pre-2020), replace `dtk-macos-aarch64` with `dtk-macos-x86_64`.
> Make sure `~/.local/bin` is in your `PATH`.

</details>

<details>
<summary><strong>🪟 On Windows</strong> (run in PowerShell):</summary>

```powershell
New-Item -ItemType Directory -Force -Path "$env:USERPROFILE\.local\bin" | Out-Null
Invoke-WebRequest -Uri "https://github.com/ssc-php/dtk/releases/latest/download/dtk-windows-x86_64.exe" -OutFile "$env:TEMP\dtk.exe"
```

Verify the checksum:

```powershell
$hash = (Get-FileHash "$env:TEMP\dtk.exe" -Algorithm SHA256).Hash.ToLower()
$expected = (Invoke-WebRequest -Uri "https://github.com/ssc-php/dtk/releases/latest/download/checksums.txt").Content -split '\r?\n' |
  Where-Object { $_ -match "dtk-windows-x86_64.exe" } | ForEach-Object { ($_ -split '\s+')[0] }
if ($hash -ne $expected) { throw "Checksum mismatch" }
```

```powershell
Move-Item "$env:TEMP\dtk.exe" "$env:USERPROFILE\.local\bin\dtk.exe"
```

> Make sure `%USERPROFILE%\.local\bin` is in your `PATH`.

</details>

### Set API tokens

DTK needs API tokens to access services such as YouTrack.
Save them once after installing:

```console
dtk tokens:save --service=youtrack
```

DTK will prompt for the token interactively (input is hidden).

Alternatively (less safe), pass it via `DTK_TOKEN` for non-interactive contexts (CI pipelines, etc):

```console
DTK_TOKEN=<your-token> dtk tokens:save --service=youtrack
```

Tokens are stored in the first available backend:

* [ ] 1Password
* [ ] HashiCorp Vault
* [x] Linux Secret Service
* [x] macOS Keychain
* [ ] Windows Credential Manager
* [x] Plain file (fallback)

See [`docs/how-to/usage/000-how-to-set-reset-rotate-tokens.md`](docs/how-to/usage/000-how-to-set-reset-rotate-tokens.md) for details.

---

## Usage

And run `dtk` without any argument to get the "help" screen:

```console
$ dtk
   ██████
 ██  ██████   DTK: Devonshire Tea caKe
████████  ██  Kanban, Git and Deployment,
 ████  ████   in one coherent flow.
   ██████
Available commands:
    ...
```

## Want to know more?

Further documentation can be found in:

* [`docs/how-to/`](docs/how-to/): how-to guides
    * [`development/`](docs/how-to/development/): local development (e.g. `001-how-to-run-qa.md`)
    * [`usage/`](docs/how-to/usage/): using DTK (e.g. `000-how-to-set-reset-rotate-tokens.md`)
* [`docs/reference/`](docs/reference/): reference docs
    * `000-token-storage.md`: storage backends, commands, security properties

You can see the current and past versions using one of the following:

* the `git tag` command
* the [releases page on Github](https://github.com/ssc-php/dtk/releases)
* the file listing the [changes between versions](CHANGELOG.md)

And finally some meta documentation:

* [copyright and MIT license](LICENSE)
* [versioning model](VERSIONING.md)
* [contribution instructions](CONTRIBUTING.md)
