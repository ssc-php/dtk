<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\File;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ssc\Dtk\Domain\Token\Composing\SaveTokenStrategy;
use Ssc\Dtk\Domain\Token\File\FileReadTokens;
use Ssc\Dtk\Domain\Token\File\FileSaveToken;
use Ssc\Dtk\Domain\Token\File\FileWriteTokens;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(FileSaveToken::class)]
final class FileSaveTokenTest extends TestCase
{
    #[TestDox("It's the last resort strategy (priority 0, executed last)")]
    public function test_it_is_the_last_resort_strategy(): void
    {
        $this->assertSame(0, FileSaveToken::priority());
    }

    #[TestDox("It always supports the current context (it's a fallback)")]
    public function test_it_always_supports_the_current_context(): void
    {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();

        $fileSaveToken = new FileSaveToken(
            new FileReadTokens($configDir),
            new FileWriteTokens($configDir),
            new NullLogger(),
            $configDir,
        );

        $this->assertTrue($fileSaveToken->supports());
        $this->assertInstanceOf(SaveTokenStrategy::class, $fileSaveToken);
    }

    #[TestDox('It saves token to file')]
    public function test_it_saves_token_to_file(): void
    {
        $configDir = sys_get_temp_dir().'/dtk-test-'.uniqid();
        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $fileSaveToken = new FileSaveToken(
            new FileReadTokens($configDir),
            new FileWriteTokens($configDir),
            new NullLogger(),
            $configDir,
        );
        $fileSaveToken->save($service, $token);

        /** @var array<string, string> $content */
        $content = json_decode((string) file_get_contents("{$configDir}/tokens.json"), true);

        $this->assertSame([$service->toString() => $token->toString()], $content);

        unlink("{$configDir}/tokens.json");
        rmdir($configDir);
    }
}
