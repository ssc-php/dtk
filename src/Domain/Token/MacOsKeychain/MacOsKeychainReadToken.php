<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\MacOsKeychain;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Platform;
use Ssc\Dtk\Domain\Token\Composing\ReadTokenStrategy;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;
use Symfony\Component\Process\Process;

/**
 * Reads tokens from macOS Keychain:
 *
 * ```
 * security find-generic-password \
 *     -a dtk \     # account (dtk)
 *     -s github \  # service (github, jira, trello, youtrack, etc)
 *     -w           # print password / secret, with no extra information
 * ```
 */
final readonly class MacOsKeychainReadToken implements ReadTokenStrategy
{
    public function __construct(
        private Platform $platform,
        private string $account = 'dtk',
    ) {
    }

    public static function priority(): int
    {
        return 100;
    }

    public function supports(): bool
    {
        return 'Darwin' === $this->platform->getOsFamily();
    }

    /**
     * @throws ValidationFailedException If the token is not found in the keychain
     */
    public function read(Service $service): Token
    {
        $process = new Process([
            'security',
            'find-generic-password',
            '-a', $this->account,
            '-s', $service->toString(),
            '-w',
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw ValidationFailedException::make(
                "Missing token for \"{$service->toString()}\": save one first with `dtk tokens:save`",
            );
        }

        return Token::fromString(trim($process->getOutput()));
    }
}
