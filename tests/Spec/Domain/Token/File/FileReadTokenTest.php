<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\File;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\Composing\ReadTokenStrategy;
use Ssc\Dtk\Domain\Token\File\FileReadToken;
use Ssc\Dtk\Domain\Token\File\FileReadTokens;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Mktemp;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\MkTempFilename;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Rmdir;

#[CoversClass(FileReadToken::class)]
final class FileReadTokenTest extends TestCase
{
    #[TestDox("It's the last resort strategy (priority 0, executed last)")]
    public function test_it_is_the_last_resort_strategy(): void
    {
        $this->assertSame(0, FileReadToken::priority());
    }

    #[TestDox("It always supports the current context (it's a fallback)")]
    public function test_it_always_supports_the_current_context(): void
    {
        $configDir = MkTempFilename::run();

        $fileReadToken = new FileReadToken(new FileReadTokens($configDir));

        $this->assertTrue($fileReadToken->supports());
        $this->assertInstanceOf(ReadTokenStrategy::class, $fileReadToken);
    }

    #[TestDox('It reads token from file')]
    public function test_it_reads_token_from_file(): void
    {
        $configDir = Mktemp::run();
        $service = ServiceFixture::make();
        $tokenValue = TokenFixture::makeString();
        file_put_contents("{$configDir}/tokens.json", json_encode([$service->toString() => $tokenValue]));

        try {
            $result = new FileReadToken(new FileReadTokens($configDir))->read($service);
            $this->assertSame($tokenValue, $result->toString());
        } finally {
            Rmdir::run($configDir);
        }
    }

    #[TestDox('It fails when: service is not found in the token file')]
    public function test_it_fails_when_service_is_missing(): void
    {
        $configDir = Mktemp::run();
        $service = ServiceFixture::make();
        file_put_contents("{$configDir}/tokens.json", json_encode([]));

        try {
            $this->expectException(ValidationFailedException::class);
            new FileReadToken(new FileReadTokens($configDir))->read($service);
        } finally {
            Rmdir::run($configDir);
        }
    }

    #[TestDox('It fails when: token file cannot be read')]
    public function test_it_fails_when_token_file_cannot_be_read(): void
    {
        $configDir = Mktemp::run();
        mkdir("{$configDir}/tokens.json"); // directory at file path makes file_get_contents fail

        try {
            $this->expectException(ServerErrorException::class);
            new FileReadToken(new FileReadTokens($configDir))->read(ServiceFixture::make());
        } finally {
            Rmdir::run($configDir);
        }
    }
}
