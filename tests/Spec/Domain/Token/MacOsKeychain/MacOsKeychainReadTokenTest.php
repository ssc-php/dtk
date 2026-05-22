<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\MacOsKeychain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Platform;
use Ssc\Dtk\Domain\Token\File\FileReadToken;
use Ssc\Dtk\Domain\Token\MacOsKeychain\MacOsKeychainReadToken;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;
use Symfony\Component\Process\Process;

#[CoversClass(MacOsKeychainReadToken::class)]
final class MacOsKeychainReadTokenTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox("It's the fallback on macOS (priority 100, executed before File)")]
    public function test_it_is_the_fallback_on_macos(): void
    {
        $this->assertSame(100, MacOsKeychainReadToken::priority());
        $this->assertGreaterThan(FileReadToken::priority(), MacOsKeychainReadToken::priority());
    }

    #[TestDox('It supports macOS: Darwin')]
    public function test_it_supports_mac_os(): void
    {
        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn('Darwin');

        $macOsKeychainReadToken = new MacOsKeychainReadToken($platform->reveal());

        $this->assertTrue($macOsKeychainReadToken->supports());
    }

    #[DataProvider('otherOsProvider')]
    #[TestDox("It doesn't support other OS: \$osFamily")]
    public function test_it_does_not_support_other_os(
        string $osFamily,
    ): void {
        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn($osFamily);

        $macOsKeychainReadToken = new MacOsKeychainReadToken($platform->reveal());

        $this->assertFalse($macOsKeychainReadToken->supports());
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

    #[TestDox('It reads token from macOS keychain [skipped when: not macOS]')]
    public function test_it_reads_token_from_mac_os_keychain(): void
    {
        if (\PHP_OS_FAMILY !== 'Darwin') {
            $this->markTestSkipped('macOS only');
        }

        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn('Darwin');

        $macOsKeychainReadToken = new MacOsKeychainReadToken($platform->reveal(), 'dtk-test');

        new Process([
            'security',
            'add-generic-password',
            '-a', 'dtk-test',
            '-s', $service->toString(),
            '-w', $token->toString(),
        ])->run();

        try {
            $result = $macOsKeychainReadToken->read($service);
            $this->assertSame($token->toString(), $result->toString());
        } finally {
            new Process([
                'security',
                'delete-generic-password',
                '-a', 'dtk-test',
                '-s', $service->toString(),
            ])->run();
        }
    }

    #[TestDox('It fails when: token is not found in the keychain [skipped when: not macOS]')]
    public function test_it_fails_when_token_is_not_found(): void
    {
        if (\PHP_OS_FAMILY !== 'Darwin') {
            $this->markTestSkipped('macOS only');
        }

        $platform = $this->prophesize(Platform::class);
        $platform->getOsFamily()->willReturn('Darwin');

        $macOsKeychainReadToken = new MacOsKeychainReadToken($platform->reveal(), 'dtk-test');

        $this->expectException(ValidationFailedException::class);
        $macOsKeychainReadToken->read(ServiceFixture::make());
    }
}
