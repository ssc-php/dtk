<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Application\TokensSave;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Ssc\Dtk\Application\TokensSave\TokensSave;
use Ssc\Dtk\Application\TokensSave\TokensSaveHandler;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\SaveToken;
use Ssc\Dtk\Domain\Token\Service;
use Ssc\Dtk\Domain\Token\Token;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(TokensSaveHandler::class)]
#[Small]
final class TokensSaveHandlerTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox('It saves the token')]
    public function test_it_saves_the_token(): void
    {
        $service = ServiceFixture::makeString();
        $token = TokenFixture::makeString();

        $saveToken = $this->prophesize(SaveToken::class);
        $saveToken->save(
            Argument::that(static fn (Service $s): bool => $s->toString() === $service),
            Argument::that(static fn (Token $t): bool => $t->toString() === $token),
        )->shouldBeCalledOnce();

        $tokensSaveHandler = new TokensSaveHandler($saveToken->reveal());
        $tokensSaveHandler->handle(new TokensSave($service, $token));
    }

    #[DataProvider('invalidParametersProvider')]
    #[TestDox('It fails on invalid parameter: $scenario')]
    public function test_it_fails_on_invalid_parameter(
        string $scenario,
        string $service,
        string $token,
    ): void {
        $saveToken = $this->prophesize(SaveToken::class);
        $saveToken->save(Argument::cetera())->shouldNotBeCalled();

        $tokensSaveHandler = new TokensSaveHandler($saveToken->reveal());

        $this->expectException(ValidationFailedException::class);
        $tokensSaveHandler->handle(new TokensSave($service, $token));
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     service: string,
     *     token: string,
     * }>
     */
    public static function invalidParametersProvider(): \Iterator
    {
        yield [
            'scenario' => 'service',
            'service' => '',
            'token' => TokenFixture::makeString(),
        ];
        yield [
            'scenario' => 'token',
            'service' => ServiceFixture::makeString(),
            'token' => '',
        ];
    }
}
