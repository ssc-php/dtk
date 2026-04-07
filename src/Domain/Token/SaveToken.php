<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token;

interface SaveToken
{
    public function save(Service $service, Token $token): void;
}
