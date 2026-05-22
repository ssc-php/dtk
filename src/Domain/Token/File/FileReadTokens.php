<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\File;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FileReadTokens
{
    public function __construct(
        #[Autowire(env: 'DTK_DATA_DIR')]
        private string $dataDir,
    ) {
    }

    /**
     * @throws ServerErrorException      If the file cannot be read
     * @throws ServerErrorException      If the JSON is invalid
     * @throws ServerErrorException      If any token value is not a string
     * @throws ValidationFailedException If any service name is not a known service
     * @throws ValidationFailedException If any token value is empty
     */
    public function read(): Tokens
    {
        $file = "{$this->dataDir}/tokens.json";
        if (!file_exists($file)) {
            return Tokens::fromArray([]);
        }

        $content = @file_get_contents($file);
        if (!\is_string($content)) {
            throw ServerErrorException::make(
                "Invalid \"tokens file\" parameter: should be readable (path: `{$file}`)",
            );
        }

        try {
            $decoded = json_decode($content, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ServerErrorException::make(
                "Invalid \"tokens file\" parameter: should contain valid JSON (path: `{$file}`)",
            );
        }

        if (!\is_array($decoded)) {
            throw ServerErrorException::make(
                "Invalid \"tokens file\" parameter: should contain valid JSON (path: `{$file}`)",
            );
        }

        return Tokens::fromArray($decoded);
    }
}
