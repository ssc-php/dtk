<?php

declare(strict_types=1);

namespace Ssc\Dtk\Infrastructure\Symfony;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * When running from a PHAR, points inside the archive (phar:// URI).
     *
     * The PHAR contains a pre-compiled Symfony container (built during `make app-phar`
     * via `cache:warmup`). PHP can require files via phar:// natively, so the container
     * is loaded directly from the archive — no runtime compilation, no filesystem writes.
     *
     * This matters because Symfony's config loader uses glob() to discover config files,
     * and glob() does not support phar:// stream wrappers. By pointing at the pre-compiled
     * container inside the PHAR, Symfony never needs to glob config/ at runtime.
     *
     * getBuildDir() inherits this override automatically, as the base Kernel defaults it to getCacheDir().
     */
    #[\Override]
    public function getCacheDir(): string
    {
        $pharPath = \Phar::running(true);
        if ('' !== $pharPath) {
            return $pharPath.'/var/cache/'.$this->environment;
        }

        return parent::getCacheDir();
    }

    /**
     * When running from a PHAR, redirects logs to a writable location on the host.
     *
     * The PHAR archive itself is read-only, so the default log path (var/log/ inside
     * the archive) cannot be written to. ~/.cache/dtk/log is used instead.
     */
    #[\Override]
    public function getLogDir(): string
    {
        if ('' !== \Phar::running(true)) {
            $home = getenv('HOME') ?: sys_get_temp_dir();

            return $home.'/.cache/dtk/log';
        }

        return parent::getLogDir();
    }
}
