<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\File;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FileWriteTokens
{
    public function __construct(
        #[Autowire(env: 'DTK_DATA_DIR')]
        private string $dataDir,
    ) {
    }

    /**
     * @param array<string, string> $tokens
     *
     * @throws ServerErrorException If the directory or file cannot be written
     */
    public function save(array $tokens): void
    {
        $doesDirExist = is_dir($this->dataDir);
        if (false === $doesDirExist) {
            $doesDirExist = @mkdir($this->dataDir, 0o700, true);
        }

        if (false === $doesDirExist) {
            throw ServerErrorException::make(
                "Invalid \"tokens directory\" parameter: should be writable (path: `{$this->dataDir}`)",
            );
        }

        $file = "{$this->dataDir}/tokens.json";
        $wasFileWrittenTo = @file_put_contents($file, json_encode($tokens, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
        if (false === $wasFileWrittenTo) {
            throw ServerErrorException::make(
                "Invalid \"tokens file\" parameter: should be writable (path: `{$file}`)",
            );
        }

        chmod($file, 0o600);
    }
}
