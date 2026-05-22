<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\Composing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Token\Composing\ComposingReadToken;
use Ssc\Dtk\Domain\Token\Composing\ReadTokenStrategy;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(ComposingReadToken::class)]
final class ComposingReadTokenTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox('It delegates to the first supporting strategy')]
    public function test_it_delegates_to_the_first_supporting_strategy(): void
    {
        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $first = $this->prophesize(ReadTokenStrategy::class);
        $first->supports()->willReturn(true);
        $first->read($service)->willReturn($token)->shouldBeCalledOnce();

        $second = $this->prophesize(ReadTokenStrategy::class);
        $second->supports()->shouldNotBeCalled();
        $second->read(Argument::cetera())->shouldNotBeCalled();

        $composingReadToken = new ComposingReadToken([$first->reveal(), $second->reveal()]);
        $result = $composingReadToken->read($service);

        $this->assertSame($token, $result);
    }

    #[TestDox('It skips non-supporting strategies')]
    public function test_it_skips_non_supporting_strategies(): void
    {
        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $first = $this->prophesize(ReadTokenStrategy::class);
        $first->supports()->willReturn(false);
        $first->read(Argument::cetera())->shouldNotBeCalled();

        $second = $this->prophesize(ReadTokenStrategy::class);
        $second->supports()->willReturn(true);
        $second->read($service)->willReturn($token)->shouldBeCalledOnce();

        $composingReadToken = new ComposingReadToken([$first->reveal(), $second->reveal()]);
        $result = $composingReadToken->read($service);

        $this->assertSame($token, $result);
    }

    #[TestDox('It fails when no strategy supports the current context')]
    public function test_it_fails_when_no_strategy_supports(): void
    {
        $service = ServiceFixture::make();

        $strategy = $this->prophesize(ReadTokenStrategy::class);
        $strategy->supports()->willReturn(false);
        $strategy->read(Argument::cetera())->shouldNotBeCalled();

        $composingReadToken = new ComposingReadToken([$strategy->reveal()]);

        $this->expectException(ServerErrorException::class);
        $composingReadToken->read($service);
    }
}
