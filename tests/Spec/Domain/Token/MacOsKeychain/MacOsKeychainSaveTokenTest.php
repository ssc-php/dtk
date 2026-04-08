<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\MacOsKeychain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ssc\Dtk\Domain\Platform;
use Ssc\Dtk\Domain\Token\File\FileSaveToken;
use Ssc\Dtk\Domain\Token\MacOsKeychain\MacOsKeychainSaveToken;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;
use Symfony\Component\Process\Process;

#[CoversClass(MacOsKeychainSaveToken::class)]
final class MacOsKeychainSaveTokenTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox("It's the fallback on macOS (priority 100, executed before File)")]
    public function test_it_is_the_fallback_on_macos(): void
    {
        $this->assertSame(100, MacOsKeychainSaveToken::priority());
        $this->assertGreaterThan(FileSaveToken::priority(), MacOsKeychainSaveToken::priority());
    }

    #[TestDox('It supports macOS: Darwin')]
    public function test_it_supports_mac_os(): void
    {
        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn('Darwin');

        $macOsKeychainSaveToken = new MacOsKeychainSaveToken(
            $platform->reveal(),
            $this->prophesize(LoggerInterface::class)->reveal(),
        );

        $this->assertTrue($macOsKeychainSaveToken->supports());
    }

    #[DataProvider('otherOsProvider')]
    #[TestDox("It doesn't support other OS: \$osFamily")]
    public function test_it_does_not_support_other_os(
        string $osFamily,
    ): void {
        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn($osFamily);

        $macOsKeychainSaveToken = new MacOsKeychainSaveToken(
            $platform->reveal(),
            $this->prophesize(LoggerInterface::class)->reveal(),
        );

        $this->assertFalse($macOsKeychainSaveToken->supports());
    }

    /**
     * @return \Iterator<array{
     *     osFamily: string,
     * }>
     */
    public static function otherOsProvider(): \Iterator
    {
        yield [
            'osFamily' => 'Linux',
        ];
        yield [
            'osFamily' => 'Windows',
        ];
        yield [
            'osFamily' => 'BSD',
        ];
        yield [
            'osFamily' => 'Solaris',
        ];
        yield [
            'osFamily' => 'Unknown',
        ];
    }

    #[TestDox('It saves token to macOS keychain [skipped when: not macOS]')]
    public function test_it_saves_token_to_mac_os_keychain(): void
    {
        if (\PHP_OS_FAMILY !== 'Darwin') {
            $this->markTestSkipped('macOS only');
        }

        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn('Darwin');

        $macOsKeychainSaveToken = new MacOsKeychainSaveToken(
            $platform->reveal(),
            new NullLogger(),
            'dtk-test',
        );

        try {
            $macOsKeychainSaveToken->save($service, $token);

            $process = new Process([
                'security',
                'find-generic-password',
                '-a', 'dtk-test',
                '-s', $service->toString(),
                '-w',
            ]);
            $process->run();

            $this->assertSame($token->toString(), trim($process->getOutput()));
        } finally {
            new Process([
                'security',
                'delete-generic-password',
                '-a', 'dtk-test',
                '-s', $service->toString(),
            ])->run();
        }
    }
}
