<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Exception;

final class ConflictException extends AppException
{
    protected const int CODE = 409;
}
