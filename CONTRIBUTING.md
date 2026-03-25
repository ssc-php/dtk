# CONTRIBUTING

Everybody should be able to help. Here's how you can make this project more
awesome:

 1. [Fork the repository](https://github.com/ssc/dtk/fork_select);
 2. create your own branch from `main`: `git checkout -b <ticket-id>/<ticket-title>` (or `<type>/<title>` if you have no ticket);
 3. make your changes and don't forget to:
    * check that the tests pass;
    * add some new tests;
    * check the coding standards;
    * look up for typos.
 4. save your changes: `git commit -am '<type>(<scope>): <description>'`;
 5. [keep your fork up-to-date](#keeping-your-fork-up-to-date);
 6. publish your changes: `git push -fu origin <your-branch-name>`;
 7. submit a [pull request](https://help.github.com/articles/creating-a-pull-request).

Your work will then be reviewed as soon as possible (suggestions about some
changes, improvements or alternatives may be given).

## Branching Model

The branching is inspired by [@jbenet](https://github.com/jbenet)
[simple git branching model](https://gist.github.com/jbenet/ee6c9ac48068889b0912):

> 1. `main` must always be deployable.
> 2. **all changes** are made through feature branches (pull-request + merge)
> 3. rebase to avoid/resolve conflicts; merge in to `main`

## Branch naming

If you have a ticket, name your branch `<ticket-id>/<ticket-title>`.
Otherwise, use a type prefix instead: `<type>/<title>`.

See the [commits section](#conventional-commits) for the list of types.

The title part should be written in lower-case with words separated by hyphens.

### Examples

* `DTK-42/fix-login-redirect`;
* `DTK-137/add-export-to-csv`;
* `fix/login-redirect`;
* `feat/export-to-csv`.

## Tests, coding standards, static analysis, etc

Run the full QA pipeline (coding standards, static analysis, automated refactoring checks, tests):

```console
make app-qa
```

Or run each tool individually:

```console
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
```

## Conventional Commits

The cleaner the git history is, the better.
See [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/).

Commit messages should follow the format `<type>(<scope>): <description>`,
where the description begins with a verb in the imperative and describes one
action (commits should be atomic: one action = one commit).

Types:

* `feat`: addition of a new feature;
* `fix`: correction of a bug or typo;
* `fix(security)`: correction of a security vulnerability;
* `perf`: improvement of performance;
* `test`: creation or correction of tests;
* `docs`: creation or correction of documentation;
* `refactor`: improvement or cleaning of code (no behaviour change);
* `chore`: any maintenance related to dependencies, config, etc.

Breaking changes (removals, incompatible API changes) are marked with `!`
after the type: `feat!: remove legacy export format`.

> Note: the changelog generator (`bin/mk-changelog.sh`) lists all `!` commits
> under the **Removed** section, regardless of their type. By convention,
> any backward-incompatible change is treated as a removal for changelog purposes.

The `<scope>` is the ticket id when available, omitted otherwise.
Types are the same as for branch naming.

### Examples

* `feat(DTK-42): add login redirect after authentication`;
* `fix(DTK-137): handle empty CSV export gracefully`;
* `feat: add login redirect after authentication`;
* `fix: handle empty CSV export gracefully`;
* `fix(security): sanitize file path in export endpoint`;
* `feat!: remove PHP 7 support`.

## Keeping your fork up-to-date

Track the upstream (original) repository:

```console
git remote add upstream https://github.com/ssc/dtk.git
```

Then, before publishing your changes, get the upstream changes:

```console
git checkout main
git pull --rebase origin main
git pull --rebase upstream main
git checkout <your-branch-name>
git rebase main
```

Your pull request will be automatically updated.
