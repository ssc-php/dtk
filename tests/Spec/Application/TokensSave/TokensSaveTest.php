<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Application\TokensSave;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Application\TokensSave\TokensSave;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(TokensSave::class)]
#[Small]
final class TokensSaveTest extends TestCase
{
    #[DataProvider('requiredParametersProvider')]
    #[TestDox('It has string parameter: $scenario')]
    public function test_it_has_string_parameters(
        string $scenario,
        string $service,
        string $token,
    ): void {
        $tokensSave = new TokensSave($service, $token);

        $this->assertSame($service, $tokensSave->service);
        $this->assertSame($token, $tokensSave->token);
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     service: string,
     *     token: string,
     * }>
     */
    public static function requiredParametersProvider(): \Iterator
    {
        yield [
            'scenario' => 'service',
            'service' => ServiceFixture::makeString(),
            'token' => TokenFixture::makeString(),
        ];
        yield [
            'scenario' => 'token',
            'service' => ServiceFixture::makeString(),
            'token' => TokenFixture::makeString(),
        ];
    }
}
