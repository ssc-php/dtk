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
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Mktemp;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\MkTempFilename;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Rmdir;

#[CoversClass(FileReadTokens::class)]
final class FileReadTokensTest extends TestCase
{
    #[TestDox('It returns tokens when: directory does not exist (`[]`)')]
    public function test_it_returns_tokens_when_directory_does_not_exist(): void
    {
        $configDir = MkTempFilename::run();

        $this->assertSame([], new FileReadTokens($configDir)->read()->toArray());
    }

    #[TestDox('It returns tokens when: file does not exist (`[]`)')]
    public function test_it_returns_tokens_when_file_does_not_exist(): void
    {
        $configDir = Mktemp::run();

        try {
            $this->assertSame([], new FileReadTokens($configDir)->read()->toArray());
        } finally {
            Rmdir::run($configDir);
        }
    }

    /** @param array<string, string> $tokens */
    #[DataProvider('tokensProvider')]
    #[TestDox('It returns tokens when: $scenario')]
    public function test_it_returns_tokens_when_file_has_tokens(
        string $scenario,
        array $tokens,
    ): void {
        $configDir = Mktemp::run();
        file_put_contents("{$configDir}/tokens.json", json_encode($tokens));

        try {
            $this->assertSame($tokens, new FileReadTokens($configDir)->read()->toArray());
        } finally {
            Rmdir::run($configDir);
        }
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     tokens: array<string, string>,
     * }>
     */
    public static function tokensProvider(): \Iterator
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
                ServiceFixture::makeString() => TokenFixture::makeString(),
                ServiceFixture::makeAnotherString() => TokenFixture::makeString(),
            ],
        ];
    }

    #[TestDox('It fails when: file contains invalid JSON')]
    public function test_it_fails_when_file_has_invalid_json(): void
    {
        $configDir = Mktemp::run();
        file_put_contents("{$configDir}/tokens.json", 'not valid json');

        $this->expectException(ServerErrorException::class);
        try {
            new FileReadTokens($configDir)->read();
        } finally {
            Rmdir::run($configDir);
        }
    }

    #[TestDox('It fails when: file cannot be read')]
    public function test_it_fails_when_file_is_not_readable(): void
    {
        $configDir = Mktemp::run();
        mkdir("{$configDir}/tokens.json"); // directory at file path makes file_get_contents fail

        $this->expectException(ServerErrorException::class);
        try {
            new FileReadTokens($configDir)->read();
        } finally {
            Rmdir::run($configDir);
        }
    }
}
