<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\MacOsKeychain;

use Psr\Log\LoggerInterface;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Platform;
use Ssc\Dtk\Domain\Token\Composing\SaveTokenStrategy;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;
use Symfony\Component\Process\Process;

/**
 * Saves tokens to macOS Keychain:
 *
 * ```
 * security add-generic-password \
 *     -a dtk \     # account (dtk)
 *     -s github \  # service (github, jira, trello, youtrack, etc)
 *     -w token \   # password / secret
 *     -U           # update item if it exists
 * ```
 *
 * > **Note**: security CLI leaks the passed token to local process inspection.
 * > Sadly there are no safer input paths for this command.
 */
final readonly class MacOsKeychainSaveToken implements SaveTokenStrategy
{
    public function __construct(
        private Platform $platform,
        private LoggerInterface $logger,
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
     * @throws ServerErrorException If the macOS keychain command fails
     */
    public function save(Service $service, Token $token): void
    {
        $this->logger->info(
            "Saving token for `{$service->toString()}` to: macOS Keychain (account: `{$this->account}`)",
        );

        $process = new Process([
            'security',
            'add-generic-password',
            '-a', $this->account,
            '-s', $service->toString(),
            '-w', $token->toString(),
            '-U',
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw ServerErrorException::make(
                "Invalid \"service\" parameter: failed to save token to macOS Keychain (service: `{$service->toString()}`)",
            );
        }
    }
}
