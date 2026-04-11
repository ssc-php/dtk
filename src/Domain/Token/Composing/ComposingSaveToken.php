<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\Composing;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Token\SaveToken;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsAlias(SaveToken::class)]
final readonly class ComposingSaveToken implements SaveToken
{
    /** @param iterable<SaveTokenStrategy> $strategies */
    public function __construct(
        #[AutowireIterator(SaveTokenStrategy::class, defaultPriorityMethod: 'priority')]
        private iterable $strategies,
    ) {
    }

    /**
     * @throws ServerErrorException If no strategy supports the current context
     */
    public function save(Service $service, Token $token): void
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports()) {
                $strategy->save($service, $token);

                return;
            }
        }

        throw ServerErrorException::make('No save token strategy supports the current context');
    }
}
