<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;

interface ReadToken
{
    /**
     * @throws ValidationFailedException If no token stored for the service
     */
    public function read(Service $service): Token;
}
