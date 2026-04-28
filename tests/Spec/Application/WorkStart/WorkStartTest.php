<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Application\WorkStart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Application\WorkStart\WorkStart;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\BranchNameFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\StartingPointFixture;

#[CoversClass(WorkStart::class)]
#[Small]
final class WorkStartTest extends TestCase
{
    #[DataProvider('requiredParametersProvider')]
    #[TestDox('It has $type parameter: $scenario')]
    public function test_it_has_parameters(
        string $type,
        string $scenario,
        string $newBranch,
        string $startingPoint,
        string $ticketId,
        bool $autostash,
    ): void {
        $workStart = new WorkStart($newBranch, $startingPoint, $ticketId, $autostash);

        $this->assertSame($newBranch, $workStart->newBranch);
        $this->assertSame($startingPoint, $workStart->startingPoint);
        $this->assertSame($ticketId, $workStart->ticketId);
        $this->assertSame($autostash, $workStart->autostash);
    }

    /**
     * @return \Iterator<array{
     *     type: string,
     *     scenario: string,
     *     newBranch: string,
     *     startingPoint: string,
     *     ticketId: string,
     *     autostash: bool,
     * }>
     */
    public static function requiredParametersProvider(): \Iterator
    {
        yield [
            'type' => 'string',
            'scenario' => 'newBranch',
            'newBranch' => BranchNameFixture::makeString(),
            'startingPoint' => StartingPointFixture::makeString(),
            'ticketId' => '',
            'autostash' => false,
        ];
        yield [
            'type' => 'string',
            'scenario' => 'startingPoint',
            'newBranch' => BranchNameFixture::makeString(),
            'startingPoint' => StartingPointFixture::makeString(),
            'ticketId' => '',
            'autostash' => false,
        ];
        yield [
            'type' => 'string',
            'scenario' => 'ticketId',
            'newBranch' => BranchNameFixture::makeString(),
            'startingPoint' => StartingPointFixture::makeString(),
            'ticketId' => 'PRJ-4423',
            'autostash' => false,
        ];
        yield [
            'type' => 'boolean',
            'scenario' => 'autostash',
            'newBranch' => BranchNameFixture::makeString(),
            'startingPoint' => StartingPointFixture::makeString(),
            'ticketId' => '',
            'autostash' => true,
        ];
    }

    #[DataProvider('defaultParametersProvider')]
    #[TestDox('It has default value for: $scenario ($default)')]
    public function test_it_has_default_parameters(
        string $scenario,
        string $default,
        mixed $expected,
        string $property,
    ): void {
        $workStart = new WorkStart(BranchNameFixture::makeString());

        $this->assertSame($expected, $workStart->{$property});
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     default: string,
     *     expected: mixed,
     *     property: string,
     * }>
     */
    public static function defaultParametersProvider(): \Iterator
    {
        yield [
            'scenario' => 'startingPoint',
            'default' => '`origin/main`',
            'expected' => 'origin/main',
            'property' => 'startingPoint',
        ];
        yield [
            'scenario' => 'ticketId',
            'default' => '`""`',
            'expected' => '',
            'property' => 'ticketId',
        ];
        yield [
            'scenario' => 'autostash',
            'default' => '`false`',
            'expected' => false,
            'property' => 'autostash',
        ];
    }
}
