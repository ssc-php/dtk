<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\LinuxSecretTool;

use Psr\Log\LoggerInterface;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Platform;
use Ssc\Dtk\Domain\Token\Composing\SaveTokenStrategy;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;
use Symfony\Component\Process\Process;

/**
 * Saves tokens to Linux Secret Service via secret-tool:
 *
 * ```
 * echo -n token | secret-tool store \  # password / secret
 *     --label 'dtk:github' \           # human-readable label (account:service)
 *     account dtk \                    # account (dtk)
 *     service github                   # service (github, jira, trello, youtrack, etc)
 * ```
 *
 * > **Note**: token is passed safely from stdin, so it doesn't leak.
 */
final readonly class LinuxSecretToolSaveToken implements SaveTokenStrategy
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
        if ('Linux' !== $this->platform->getOsFamily()) {
            return false;
        }

        $process = new Process(['which', 'secret-tool']);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * @throws ServerErrorException If the secret-tool command fails
     */
    public function save(Service $service, Token $token): void
    {
        $this->logger->info(
            "Saving token for `{$service->toString()}` to: Linux Secret Service (account: `{$this->account}`)",
        );

        $process = new Process([
            'secret-tool',
            'store',
            '--label', "{$this->account}:{$service->toString()}",
            'account', $this->account,
            'service', $service->toString(),
        ]);
        $process->setInput($token->toString());
        $process->run();

        if (!$process->isSuccessful()) {
            throw ServerErrorException::make(
                "Invalid \"service\" parameter: failed to save token to Linux Secret Service (service: `{$service->toString()}`)",
            );
        }
    }
}
