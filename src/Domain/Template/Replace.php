<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Template;

/**
 * @object-type Service
 */
final readonly class Replace
{
    /** @param array<string, string> $parameters */
    public function in(string $template, array $parameters): string
    {
        $placeholders = [];
        $values = [];
        foreach ($parameters as $placeholder => $value) {
            $placeholders[] = "{{$placeholder}}";
            $values[] = $value;
        }

        return str_replace($placeholders, $values, $template);
    }
}
