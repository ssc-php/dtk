<?php

declare(strict_types=1);

namespace Ssc\Dtk\Application\TokensSave;

/**
 * @object-type DataTransferObject
 */
final readonly class TokensSave
{
    public function __construct(
        public string $service,
        public string $token,
    ) {
    }
}
