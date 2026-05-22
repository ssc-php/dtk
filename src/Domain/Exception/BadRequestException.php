<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Exception;

final class BadRequestException extends AppException
{
    protected const int CODE = 400;
}
