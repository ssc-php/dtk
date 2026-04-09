# How to run QA

> Note: this is for local development.

## Run the full pipeline

```console
make app-qa
```

This runs in order: `composer-dump`, `cs-check`, `phpstan-analyze`, `rector-check`, `phpunit`.
It'll take care of the Symfony cache (`bin/sfcc-if-stale.sh`).

## Run individual tools

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

# PSR-4 compliance check (FQCN, classnames, filenames, namespaces)
make composer-dump
```
