<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\LinuxSecretTool;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Platform;
use Ssc\Dtk\Domain\Token\File\FileReadToken;
use Ssc\Dtk\Domain\Token\LinuxSecretTool\LinuxSecretToolReadToken;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;
use Symfony\Component\Process\Process;

#[CoversClass(LinuxSecretToolReadToken::class)]
final class LinuxSecretToolReadTokenTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox("It's the fallback on Linux (priority 100, executed before File)")]
    public function test_it_is_the_fallback_on_linux(): void
    {
        $this->assertSame(100, LinuxSecretToolReadToken::priority());
        $this->assertGreaterThan(FileReadToken::priority(), LinuxSecretToolReadToken::priority());
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

        $linuxSecretToolReadToken = new LinuxSecretToolReadToken($platform->reveal());

        $this->assertTrue($linuxSecretToolReadToken->supports());
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

        $linuxSecretToolReadToken = new LinuxSecretToolReadToken($platform->reveal());

        $this->assertFalse($linuxSecretToolReadToken->supports());
    }

    #[DataProvider('otherOsProvider')]
    #[TestDox("It doesn't support other OS: \$osFamily")]
    public function test_it_does_not_support_other_os(
        string $osFamily,
    ): void {
        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn($osFamily);

        $linuxSecretToolReadToken = new LinuxSecretToolReadToken($platform->reveal());

        $this->assertFalse($linuxSecretToolReadToken->supports());
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

    #[TestDox('It reads token from Linux: with secret-tool [skipped when: not Linux, secret-tool absent]')]
    public function test_it_reads_token_from_linux_secret_service(): void
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

        $linuxSecretToolReadToken = new LinuxSecretToolReadToken($platform->reveal(), 'dtk-test');

        $store = new Process([
            'secret-tool', 'store',
            '--label', 'dtk-test',
            'service', $service->toString(),
            'account', 'dtk-test',
        ]);
        $store->setInput($token->toString());
        $store->run();

        try {
            $result = $linuxSecretToolReadToken->read($service);
            $this->assertSame($token->toString(), $result->toString());
        } finally {
            new Process([
                'secret-tool',
                'clear',
                'account', 'dtk-test',
                'service', $service->toString(),
            ])->run();
        }
    }

    #[TestDox('It fails when: token is not found in the secret store [skipped when: not Linux, secret-tool absent]')]
    public function test_it_fails_when_token_is_not_found(): void
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

        $linuxSecretToolReadToken = new LinuxSecretToolReadToken($platform->reveal(), 'dtk-test');

        $this->expectException(ValidationFailedException::class);
        $linuxSecretToolReadToken->read(ServiceFixture::make());
    }
}
