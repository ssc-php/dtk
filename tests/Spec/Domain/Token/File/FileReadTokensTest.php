<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\File;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Token\File\FileReadTokens;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(FileReadTokens::class)]
final class FileReadTokensTest extends TestCase
{
    #[TestDox('It returns tokens when: directory does not exist (`[]`)')]
    public function test_it_returns_tokens_when_directory_does_not_exist(): void
    {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();

        $this->assertSame([], new FileReadTokens($configDir)->get());
    }

    #[TestDox('It returns tokens when: file does not exist (`[]`)')]
    public function test_it_returns_tokens_when_file_does_not_exist(): void
    {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        mkdir($configDir, 0o700, true);

        $this->assertSame([], new FileReadTokens($configDir)->get());

        rmdir($configDir);
    }

    /** @param array<string, string> $tokens */
    #[DataProvider('tokensProvider')]
    #[TestDox('It returns tokens when: $scenario')]
    public function test_it_returns_tokens_when_file_has_tokens(
        string $scenario,
        array $tokens,
    ): void {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        mkdir($configDir, 0o700, true);
        file_put_contents("{$configDir}/tokens.json", json_encode($tokens));
        $this->assertSame($tokens, new FileReadTokens($configDir)->get());
        unlink("{$configDir}/tokens.json");
        rmdir($configDir);
    }

    /**
     * @return \Generator<array{
     *     scenario: string,
     *     tokens: array<string, string>,
     * }>
     */
    public static function tokensProvider(): \Generator
    {
        yield [
            'scenario' => 'no tokens (`[]`)',
            'tokens' => [],
        ];
        yield [
            'scenario' => "one token (`['service' => 'token']`)",
            'tokens' => [ServiceFixture::makeString() => TokenFixture::makeString()],
        ];
        yield [
            'scenario' => "many tokens (`['s1' => 't1', 's2' => 't2']`)",
            'tokens' => [
                'service-a' => TokenFixture::makeString(),
                'service-b' => TokenFixture::makeString(),
            ],
        ];
    }

    #[TestDox('It fails when: file cannot be read')]
    public function test_it_fails_when_file_is_not_readable(): void
    {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        mkdir($configDir, 0o700, true);
        mkdir("{$configDir}/tokens.json"); // directory at file path makes file_get_contents fail

        $this->expectException(ServerErrorException::class);
        try {
            new FileReadTokens($configDir)->get();
        } finally {
            rmdir("{$configDir}/tokens.json");
            rmdir($configDir);
        }
    }

    #[TestDox('It fails when: file contains invalid data (`not valid json`)')]
    public function test_it_fails_when_file_contains_invalid_data(): void
    {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        mkdir($configDir, 0o700, true);
        file_put_contents("{$configDir}/tokens.json", 'not valid json');

        $this->expectException(ServerErrorException::class);
        try {
            new FileReadTokens($configDir)->get();
        } finally {
            unlink("{$configDir}/tokens.json");
            rmdir($configDir);
        }
    }

    #[DataProvider('invalidTokensProvider')]
    #[TestDox('It fails when: file contains non string $scenario')]
    public function test_it_fails_when_file_contains_non_string_tokens(
        string $scenario,
        string $content,
    ): void {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        mkdir($configDir, 0o700, true);
        file_put_contents("{$configDir}/tokens.json", $content);
        $this->expectException(ServerErrorException::class);
        try {
            new FileReadTokens($configDir)->get();
        } finally {
            unlink("{$configDir}/tokens.json");
            rmdir($configDir);
        }
    }

    /**
     * @return \Generator<array{
     *     scenario: string,
     *     content: string,
     * }>
     */
    public static function invalidTokensProvider(): \Generator
    {
        yield [
            'scenario' => 'service (`["token"]`, integer key)',
            'content' => '["token"]',
        ];
        yield [
            'scenario' => 'token (`{"service": 123}`)',
            'content' => '{"service": 123}',
        ];
    }
}
