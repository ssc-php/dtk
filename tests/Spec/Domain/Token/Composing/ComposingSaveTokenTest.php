<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Token\Composing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Ssc\Dtk\Domain\Exception\ServerErrorException;
use Ssc\Dtk\Domain\Token\Composing\ComposingSaveToken;
use Ssc\Dtk\Domain\Token\Composing\SaveTokenStrategy;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;

#[CoversClass(ComposingSaveToken::class)]
final class ComposingSaveTokenTest extends TestCase
{
    use ProphecyTrait;

    #[TestDox('It delegates to the first supporting strategy')]
    public function test_it_delegates_to_the_first_supporting_strategy(): void
    {
        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $first = $this->prophesize(SaveTokenStrategy::class);
        $first->supports()->willReturn(true);
        $first->save($service, $token)->shouldBeCalledOnce();

        $second = $this->prophesize(SaveTokenStrategy::class);
        $second->supports()->shouldNotBeCalled();
        $second->save(Argument::cetera())->shouldNotBeCalled();

        $composingSaveToken = new ComposingSaveToken([$first->reveal(), $second->reveal()]);
        $composingSaveToken->save($service, $token);
    }

    #[TestDox('It skips non-supporting strategies')]
    public function test_it_skips_non_supporting_strategies(): void
    {
        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $first = $this->prophesize(SaveTokenStrategy::class);
        $first->supports()->willReturn(false);
        $first->save(Argument::cetera())->shouldNotBeCalled();

        $second = $this->prophesize(SaveTokenStrategy::class);
        $second->supports()->willReturn(true);
        $second->save($service, $token)->shouldBeCalledOnce();

        $composingSaveToken = new ComposingSaveToken([$first->reveal(), $second->reveal()]);
        $composingSaveToken->save($service, $token);
    }

    #[TestDox('It fails when no strategy supports the current context')]
    public function test_it_fails_when_no_strategy_supports(): void
    {
        $service = ServiceFixture::make();
        $token = TokenFixture::make();

        $strategy = $this->prophesize(SaveTokenStrategy::class);
        $strategy->supports()->willReturn(false);
        $strategy->save(Argument::cetera())->shouldNotBeCalled();

        $composingSaveToken = new ComposingSaveToken([$strategy->reveal()]);

        $this->expectException(ServerErrorException::class);
        $composingSaveToken->save($service, $token);
    }
}
