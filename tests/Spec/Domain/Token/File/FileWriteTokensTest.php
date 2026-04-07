<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\File;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Token\File\FileWriteTokens;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(FileWriteTokens::class)]
final class FileWriteTokensTest extends TestCase
{
    /**
     * @param \Closure(string $configDir, array<string, string> $tokens): void $assert
     */
    #[DataProvider('directoryNotExistProvider')]
    #[TestDox('It writes tokens when: directory does not exist $scenario')]
    public function test_it_writes_tokens_when_directory_does_not_exist(
        string $scenario,
        \Closure $assert,
    ): void {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        $tokens = [ServiceFixture::makeString() => TokenFixture::makeString()];

        new FileWriteTokens($configDir)->save($tokens);

        $assert($configDir, $tokens);

        unlink("{$configDir}/tokens.json");
        rmdir($configDir);
    }

    /**
     * @return \Generator<array{
     *     scenario: string,
     *     assert: \Closure(string, array<string, string>): void,
     * }>
     */
    public static function directoryNotExistProvider(): \Generator
    {
        yield [
            'scenario' => '(creates directory, 0700 permissions)',
            'assert' => static function (string $configDir, array $tokens): void {
                self::assertDirectoryExists($configDir);
                self::assertSame(0o700, fileperms($configDir) & 0o777);
            },
        ];
        yield [
            'scenario' => '(creates file, 0600 permissions)',
            'assert' => static function (string $configDir, array $tokens): void {
                self::assertFileExists("{$configDir}/tokens.json");
                self::assertSame(0o600, fileperms("{$configDir}/tokens.json") & 0o777);
            },
        ];
        yield [
            'scenario' => '(writes tokens)',
            'assert' => static function (string $configDir, array $tokens): void {
                /** @var array<string, string> $content */
                $content = json_decode((string) file_get_contents("{$configDir}/tokens.json"), true);
                self::assertSame($tokens, $content);
            },
        ];
    }

    /**
     * @param \Closure(string $configDir, array<string, string> $tokens): void $assert
     */
    #[DataProvider('fileNotExistProvider')]
    #[TestDox('It writes tokens when: file does not exist $scenario')]
    public function test_it_writes_tokens_when_file_does_not_exist(
        string $scenario,
        \Closure $assert,
    ): void {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        $tokens = [ServiceFixture::makeString() => TokenFixture::makeString()];
        mkdir($configDir, 0o700, true);

        new FileWriteTokens($configDir)->save($tokens);

        $assert($configDir, $tokens);

        unlink("{$configDir}/tokens.json");
        rmdir($configDir);
    }

    /**
     * @return \Generator<array{
     *     scenario: string,
     *     assert: \Closure(string, array<string, string>): void,
     * }>
     */
    public static function fileNotExistProvider(): \Generator
    {
        yield [
            'scenario' => '(creates file, 0600 permissions)',
            'assert' => static function (string $configDir, array $tokens): void {
                self::assertFileExists("{$configDir}/tokens.json");
                self::assertSame(0o600, fileperms("{$configDir}/tokens.json") & 0o777);
            },
        ];
        yield [
            'scenario' => '(writes tokens)',
            'assert' => static function (string $configDir, array $tokens): void {
                /** @var array<string, string> $content */
                $content = json_decode((string) file_get_contents("{$configDir}/tokens.json"), true);
                self::assertSame($tokens, $content);
            },
        ];
    }

    /** @param array<string, string> $tokens */
    #[DataProvider('tokensProvider')]
    #[TestDox('It writes tokens: $scenario')]
    public function test_it_writes_tokens(
        string $scenario,
        array $tokens,
    ): void {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();

        new FileWriteTokens($configDir)->save($tokens);

        /** @var array<string, string> $content */
        $content = json_decode((string) file_get_contents("{$configDir}/tokens.json"), true);
        $this->assertSame($tokens, $content);

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

    /** @param array<string, string> $tokens */
    #[DataProvider('tokenInFileProvider')]
    #[TestDox('It writes tokens when: $scenario')]
    public function test_it_writes_tokens_when_token_in_file(
        string $scenario,
        string $initialContent,
        array $tokens,
    ): void {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        mkdir($configDir, 0o700, true);
        file_put_contents("{$configDir}/tokens.json", $initialContent);

        new FileWriteTokens($configDir)->save($tokens);

        /** @var array<string, string> $content */
        $content = json_decode((string) file_get_contents("{$configDir}/tokens.json"), true);
        $this->assertSame($tokens, $content);

        unlink("{$configDir}/tokens.json");
        rmdir($configDir);
    }

    /**
     * @return \Generator<array{
     *     scenario: string,
     *     initialContent: string,
     *     tokens: array<string, string>,
     * }>
     */
    public static function tokenInFileProvider(): \Generator
    {
        $service = ServiceFixture::makeString();

        yield [
            'scenario' => 'token already exists (overwrites it)',
            'initialContent' => json_encode([$service => 'old-token'], \JSON_THROW_ON_ERROR),
            'tokens' => [$service => TokenFixture::makeString()],
        ];
        yield [
            'scenario' => 'token does not exist yet (adds it)',
            'initialContent' => '{}',
            'tokens' => [ServiceFixture::makeString() => TokenFixture::makeString()],
        ];
    }

    #[TestDox('It fails when: directory cannot be created')]
    public function test_it_fails_when_directory_cannot_be_created(): void
    {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        file_put_contents($configDir, ''); // file at directory path makes mkdir fail

        $this->expectException(ServerErrorException::class);
        try {
            new FileWriteTokens($configDir)->save([]);
        } finally {
            unlink($configDir);
        }
    }

    #[TestDox('It fails when: file cannot be written')]
    public function test_it_fails_when_file_cannot_be_written(): void
    {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        mkdir($configDir, 0o700, true);
        mkdir("{$configDir}/tokens.json"); // directory at file path makes file_put_contents fail

        $this->expectException(ServerErrorException::class);
        try {
            new FileWriteTokens($configDir)->save([]);
        } finally {
            rmdir("{$configDir}/tokens.json");
            rmdir($configDir);
        }
    }
}
