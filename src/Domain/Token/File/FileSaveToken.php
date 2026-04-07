<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\File;

use Psr\Log\LoggerInterface;
use Ssc\Dtk\Domain\Token\Composing\SaveTokenStrategy;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Saves tokens to a plain file (fallback when no OS keyring is available):
 *
 * ```
 * $DTK_DATA_DIR/tokens.json:
 * {
 *     "github": "token"  # service: password / secret
 * }
 * ```
 *
 * > **Note**: token is stored as plain text, so it leaks to anyone with read access to the file.
 */
final readonly class FileSaveToken implements SaveTokenStrategy
{
    public function __construct(
        private FileReadTokens $fileReadTokens,
        private FileWriteTokens $fileWriteTokens,
        private LoggerInterface $logger,
        #[Autowire(env: 'DTK_DATA_DIR')]
        private string $dataDir,
    ) {
    }

    public static function priority(): int
    {
        return 0;
    }

    public function supports(): bool
    {
        return true;
    }

    public function save(Service $service, Token $token): void
    {
        $this->logger->warning(
            'Warning "token" parameter: should be stored securely in OS keyring'
            .' but none is supported on this platform, falling back to unsafe plain text file',
        );
        $this->logger->info(
            "Saving token for `{$service->toString()}` to: `{$this->dataDir}/tokens.json`",
        );

        $tokens = $this->fileReadTokens->get();

        $tokens[$service->toString()] = $token->toString();

        $this->fileWriteTokens->save($tokens);
    }
}
