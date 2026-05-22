<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Kanban\Ticket;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Kanban\Ticket\Slug;

#[CoversClass(Slug::class)]
#[Small]
final class SlugTest extends TestCase
{
    #[DataProvider('slugProvider')]
    #[TestDox('It can be converted from a string: $scenario')]
    public function test_it_can_be_converted_from_a_string(
        string $scenario,
        string $rawValue,
        string $expectedSlug,
    ): void {
        $this->assertSame($expectedSlug, Slug::fromString($rawValue)->toString());
    }

    /**
     * @return \Iterator<array{scenario: string, rawValue: string, expectedSlug: string}>
     */
    public static function slugProvider(): \Iterator
    {
        $rawValue = 'Fix broken login';
        $expectedSlug = 'fix-broken-login';
        yield [
            'scenario' => "kebab case (`{$rawValue}` => `{$expectedSlug}`)",
            'rawValue' => $rawValue,
            'expectedSlug' => $expectedSlug,
        ];
        $rawValue = '';
        $expectedSlug = '';
        yield [
            'scenario' => "empty (`{$rawValue}` => `{$expectedSlug}`)",
            'rawValue' => $rawValue,
            'expectedSlug' => $expectedSlug,
        ];
        $rawValue = 'Login';
        $expectedSlug = 'login';
        yield [
            'scenario' => "one word (`{$rawValue}` => `{$expectedSlug}`)",
            'rawValue' => $rawValue,
            'expectedSlug' => $expectedSlug,
        ];
        $rawValue = 'Fix   broken  login';
        $expectedSlug = 'fix-broken-login';
        yield [
            'scenario' => "whitespaces (`{$rawValue}` => `{$expectedSlug}`)",
            'rawValue' => $rawValue,
            'expectedSlug' => $expectedSlug,
        ];
        $rawValue = '  Fix broken login  ';
        yield [
            'scenario' => "trailing whitespaces (`{$rawValue}` => `{$expectedSlug}`)",
            'rawValue' => $rawValue,
            'expectedSlug' => $expectedSlug,
        ];
        $rawValue = "Treiñ al lec'hienn web e brezhoneg";
        $expectedSlug = 'trein-al-lec-hienn-web-e-brezhoneg';
        yield [
            'scenario' => "unicode (`{$rawValue}` => `{$expectedSlug}`)",
            'rawValue' => $rawValue,
            'expectedSlug' => $expectedSlug,
        ];
        $rawValue = '---';
        $expectedSlug = '';
        yield [
            'scenario' => "no alphanumericals (`{$rawValue}` => `{$expectedSlug}`)",
            'rawValue' => $rawValue,
            'expectedSlug' => $expectedSlug,
        ];
    }
}
