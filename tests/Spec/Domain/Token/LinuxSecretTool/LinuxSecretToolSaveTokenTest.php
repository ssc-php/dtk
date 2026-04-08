<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\LinuxSecretTool;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ssc\Dtk\Domain\Platform;
use Ssc\Dtk\Domain\Token\File\FileSaveToken;
use Ssc\Dtk\Domain\Token\LinuxSecretTool\LinuxSecretToolSaveToken;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;
use Symfony\Component\Process\Process;

#[CoversClass(LinuxSecretToolSaveToken::class)]
final class LinuxSecretToolSaveTokenTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox("It's the fallback on Linux (priority 100, executed before File)")]
    public function test_it_is_the_fallback_on_linux(): void
    {
        $this->assertSame(100, LinuxSecretToolSaveToken::priority());
        $this->assertGreaterThan(FileSaveToken::priority(), LinuxSecretToolSaveToken::priority());
    }

    #[TestDox('It supports Linux: with secret-tool [skipped when: not Linux, secret-tool absent]')]
    public function test_it_supports_linux(): void
    {
        if (\PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('Linux only');
        }

        $process = new Process(['which', 'secret-tool']);
        $process->run();
        if (!$process->isSuccessful()) {
            $this->markTestSkipped('secret-tool not available');
        }

        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn('Linux');

        $linuxSecretToolSaveToken = new LinuxSecretToolSaveToken(
            $platform->reveal(),
            $this->prophesize(LoggerInterface::class)->reveal(),
        );

        $this->assertTrue($linuxSecretToolSaveToken->supports());
    }

    #[TestDox("It doesn't support Linux: without secret-tool [skipped when: not Linux, secret-tool found]")]
    public function test_it_does_not_support_linux_without_secret_tool(): void
    {
        if (\PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('Linux only');
        }

        $process = new Process(['which', 'secret-tool']);
        $process->run();
        if ($process->isSuccessful()) {
            $this->markTestSkipped('secret-tool is available');
        }

        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn('Linux');

        $linuxSecretToolSaveToken = new LinuxSecretToolSaveToken(
            $platform->reveal(),
            $this->prophesize(LoggerInterface::class)->reveal(),
        );

        $this->assertFalse($linuxSecretToolSaveToken->supports());
    }

    #[DataProvider('otherOsProvider')]
    #[TestDox("It doesn't support other OS: \$osFamily")]
    public function test_it_does_not_support_other_os(
        string $osFamily,
    ): void {
        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn($osFamily);

        $linuxSecretToolSaveToken = new LinuxSecretToolSaveToken(
            $platform->reveal(),
            $this->prophesize(LoggerInterface::class)->reveal(),
        );

        $this->assertFalse($linuxSecretToolSaveToken->supports());
    }

    /**
     * @return \Iterator<array{
     *     osFamily: string,
     * }>
     */
    public static function otherOsProvider(): \Iterator
    {
        yield [
            'osFamily' => 'BSD',
        ];
        yield [
            'osFamily' => 'Darwin',
        ];
        yield [
            'osFamily' => 'Solaris',
        ];
        yield [
            'osFamily' => 'Unknown',
        ];
        yield [
            'osFamily' => 'Windows',
        ];
    }

    #[TestDox('It saves token to Linux: with secret-tool [skipped when: not Linux, secret-tool absent]')]
    public function test_it_saves_token_to_linux_secret_service(): void
    {
        if (\PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('Linux only');
        }

        $which = new Process(['which', 'secret-tool']);
        $which->run();
        if (!$which->isSuccessful()) {
            $this->markTestSkipped('secret-tool not available');
        }

        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn('Linux');

        $linuxSecretToolSaveToken = new LinuxSecretToolSaveToken(
            $platform->reveal(),
            new NullLogger(),
            'dtk-test',
        );

        try {
            $linuxSecretToolSaveToken->save($service, $token);

            $process = new Process([
                'secret-tool',
                'lookup',
                'account', 'dtk-test',
                'service', $service->toString(),
            ]);
            $process->run();

            $this->assertSame($token->toString(), trim($process->getOutput()));
        } finally {
            new Process([
                'secret-tool',
                'clear',
                'account', 'dtk-test',
                'service', $service->toString(),
            ])->run();
        }
    }
}
