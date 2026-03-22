<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

/**
 * Compiles .env files into .env.local.php for production use.
 *
 * Replicates what `composer dump-env prod` (symfony/flex) does, without
 * requiring symfony/flex as a dependency.
 *
 * Symfony's Dotenv::loadEnv() loads .env, .env.local, .env.prod, etc. in the
 * correct order of precedence, populates $_ENV, and records which keys it set
 * in SYMFONY_DOTENV_VARS. We filter to only those keys so that unrelated shell
 * environment variables are not baked into the compiled file.
 */

require __DIR__.'/../../vendor/autoload.php';

new Dotenv()->loadEnv('.env');

$vars = array_filter(
    $_ENV,
    static fn (string $k): bool => in_array($k, explode(',', $_SERVER['SYMFONY_DOTENV_VARS'] ?? ''), true),
    \ARRAY_FILTER_USE_KEY,
);

file_put_contents('.env.local.php', '<?php return '.var_export($vars, true).';'.\PHP_EOL);
