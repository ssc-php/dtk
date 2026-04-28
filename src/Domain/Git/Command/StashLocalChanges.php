<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Git\Command;

use Ssc\Dtk\Domain\Exception\ServerErrorException;

/**
 * @object-type Service
 */
interface StashLocalChanges
{
    /**
     * @return bool true if local changes were stashed, false if there were none
     *
     * @throws ServerErrorException If git stash fails
     */
    public function stash(): bool;
}
