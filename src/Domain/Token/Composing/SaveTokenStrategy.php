<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\Composing;

use Ssc\Dtk\Domain\Token\SaveToken;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface SaveTokenStrategy extends SaveToken
{
    /**
     * Higher values are executed first, lower values last.
     * Use 0 for last resort fallback strategies.
     */
    public static function priority(): int;

    public function supports(): bool;
}
