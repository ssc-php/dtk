<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\LinuxSecretTool;

use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Platform;
use Ssc\Dtk\Domain\Token\Composing\ReadTokenStrategy;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;
use Symfony\Component\Process\Process;

/**
 * Reads tokens from Linux Secret Service via secret-tool:
 *
 * ```
 * secret-tool lookup \
 *     account dtk \     # account (dtk)
 *     service github    # service (github, jira, trello, youtrack, etc)
 * ```
 */
final readonly class LinuxSecretToolReadToken implements ReadTokenStrategy
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
        if ('Linux' !== $this->platform->getOsFamily()) {
            return false;
        }

        $process = new Process(['which', 'secret-tool']);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * @throws ValidationFailedException If the token is not found in the secret store
     */
    public function read(Service $service): Token
    {
        $process = new Process([
            'secret-tool',
            'lookup',
            'account', $this->account,
            'service', $service->toString(),
        ]);
        $process->run();

        if (!$process->isSuccessful() || '' === trim($process->getOutput())) {
            throw ValidationFailedException::make(
                "Missing token for \"{$service->toString()}\": save one first with `dtk tokens:save`",
            );
        }

        return Token::fromString(trim($process->getOutput()));
    }
}
