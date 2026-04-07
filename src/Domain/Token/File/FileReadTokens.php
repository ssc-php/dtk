<?php

declare(strict_types=1);

namespace Ssc\Dtk\Domain\Token\File;

use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FileReadTokens
{
    public function __construct(
        #[Autowire(env: 'DTK_DATA_DIR')]
        private string $dataDir,
    ) {
    }

    /**
     * @return array<string, string>
     *
     * @throws ServerErrorException If the file cannot be read or contains invalid data
     */
    public function get(): array
    {
        $file = "{$this->dataDir}/tokens.json";
        if (!file_exists($file)) {
            return [];
        }

        $content = @file_get_contents($file);
        if (!\is_string($content)) {
            throw ServerErrorException::make(
                "Invalid \"tokens file\" parameter: should be readable (path: `{$file}`)",
            );
        }

        $json = json_decode($content, true);
        if (!\is_array($json)) {
            throw ServerErrorException::make(
                "Invalid \"tokens file\" parameter: should contain valid JSON (path: `{$file}`)",
            );
        }

        // Explicit assignments let PHPStan infer array<string, string>
        $rawTokens = [];
        foreach ($json as $rawService => $rawToken) {
            if (!\is_string($rawService)) {
                throw ServerErrorException::make(
                    "Invalid \"service\" parameter: should be a string (file path: `{$file}`)",
                );
            }

            if (!\is_string($rawToken)) {
                throw ServerErrorException::make(
                    "Invalid \"token\" parameter: should be a string (file path: `{$file}`, key: `{$rawService}`)",
                );
            }

            $rawTokens[$rawService] = $rawToken;
        }

        return $rawTokens;
    }
}
