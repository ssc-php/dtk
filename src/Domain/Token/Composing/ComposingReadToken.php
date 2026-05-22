<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\Composing;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Token\ReadToken;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsAlias(ReadToken::class)]
final readonly class ComposingReadToken implements ReadToken
{
    /** @param iterable<ReadTokenStrategy> $strategies */
    public function __construct(
        #[AutowireIterator(ReadTokenStrategy::class, defaultPriorityMethod: 'priority')]
        private iterable $strategies,
    ) {
    }

    /**
     * @throws ServerErrorException If no strategy supports the current context
     */
    public function read(Service $service): Token
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports()) {
                return $strategy->read($service);
            }
        }

        throw ServerErrorException::make('No read token strategy supports the current context');
    }
}
