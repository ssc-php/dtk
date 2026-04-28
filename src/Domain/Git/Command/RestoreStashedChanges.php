<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Git\Command;

use Ssc\Dtk\Domain\Exception\ServerErrorException;

/**
 * @object-type Service
 */
interface RestoreStashedChanges
{
    /**
     * @throws ServerErrorException If git stash pop fails
     */
    public function restore(): void;
}
