<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Spec\Domain\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Domain\Template\Replace;

#[CoversClass(Replace::class)]
#[Small]
final class ReplaceTest extends TestCase
{
    /** @param array<string, string> $parameters */
    #[DataProvider('templateProvider')]
    #[TestDox('It replaces matching placeholders in template: $scenario (`$template`, `$jsonParameters`: `$expected`)')]
    public function test_it_replaces_matching_placeholders_in_template(
        string $scenario,
        string $jsonParameters,
        string $template,
        array $parameters,
        string $expected,
    ): void {
        $replace = new Replace();

        $this->assertSame($expected, $replace->in($template, $parameters));
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     jsonParameters: string,
     *     template: string,
     *     parameters: array<string, string>,
     *     expected: string,
     * }>
     */
    public static function templateProvider(): \Iterator
    {
        $parameters = ['id' => 'PRJ-4423'];
        yield [
            'scenario' => '1 placeholder resolved',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/cunning-plan',
            'parameters' => $parameters,
            'expected' => 'PRJ-4423/feat/cunning-plan',
        ];

        $parameters = ['id' => 'PRJ-4423'];
        yield [
            'scenario' => 'many placeholders, some resolved',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/{title}',
            'parameters' => $parameters,
            'expected' => 'PRJ-4423/feat/{title}',
        ];

        $parameters = ['id' => 'PRJ-4423', 'title' => 'cunning-plan'];
        yield [
            'scenario' => 'many placeholders, all resolved',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/{title}',
            'parameters' => $parameters,
            'expected' => 'PRJ-4423/feat/cunning-plan',
        ];

        $parameters = ['id' => 'PRJ-4423'];
        yield [
            'scenario' => 'repeated placeholder',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/{id}',
            'parameters' => $parameters,
            'expected' => 'PRJ-4423/feat/PRJ-4423',
        ];

        $parameters = ['id' => ''];
        yield [
            'scenario' => 'empty string value',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/cunning-plan',
            'parameters' => $parameters,
            'expected' => '/feat/cunning-plan',
        ];

        $parameters = ['id' => 'PRJ-4423'];
        yield [
            'scenario' => 'template is only a placeholder',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}',
            'parameters' => $parameters,
            'expected' => 'PRJ-4423',
        ];

        $parameters = ['id' => '{blackadder}'];
        yield [
            'scenario' => 'value containing placeholder syntax',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat',
            'parameters' => $parameters,
            'expected' => '{blackadder}/feat',
        ];

        $parameters = ['a' => '{b}', 'b' => 'something'];
        yield [
            'scenario' => 'value containing another parameter placeholder',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{a}/feat',
            'parameters' => $parameters,
            'expected' => 'something/feat',
        ];

        $parameters = ['b' => 'something', 'a' => '{b}'];
        yield [
            'scenario' => 'value containing already-processed parameter placeholder',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{a}/feat',
            'parameters' => $parameters,
            'expected' => '{b}/feat',
        ];
    }

    /** @param array<string, string> $parameters */
    #[DataProvider('noMatchProvider')]
    #[TestDox('It leaves unmatched placeholders in template: $scenario (`$template`, `$jsonParameters`: `$expected`)')]
    public function test_it_leaves_unmatched_placeholders_in_template(
        string $scenario,
        string $jsonParameters,
        string $template,
        array $parameters,
        string $expected,
    ): void {
        $replace = new Replace();

        $this->assertSame($expected, $replace->in($template, $parameters));
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     jsonParameters: string,
     *     template: string,
     *     parameters: array<string, string>,
     *     expected: string,
     * }>
     */
    public static function noMatchProvider(): \Iterator
    {
        $parameters = [];
        yield [
            'scenario' => 'no parameters',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/cunning-plan',
            'parameters' => $parameters,
            'expected' => '{id}/feat/cunning-plan',
        ];

        $parameters = ['title' => 'cunning-plan'];
        yield [
            'scenario' => 'no matching parameters',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/turnips',
            'parameters' => $parameters,
            'expected' => '{id}/feat/turnips',
        ];

        $parameters = ['id' => 'PRJ-4423'];
        yield [
            'scenario' => 'many placeholders, some unmatched',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/{title}',
            'parameters' => $parameters,
            'expected' => 'PRJ-4423/feat/{title}',
        ];
    }

    /** @param array<string, string> $parameters */
    #[DataProvider('ignoresParametersProvider')]
    #[TestDox('It ignores unmatched parameters: $scenario (`$template`, `$jsonParameters`: `$expected`)')]
    public function test_it_ignores_unmatched_parameters(
        string $scenario,
        string $jsonParameters,
        string $template,
        array $parameters,
        string $expected,
    ): void {
        $replace = new Replace();

        $this->assertSame($expected, $replace->in($template, $parameters));
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     jsonParameters: string,
     *     template: string,
     *     parameters: array<string, string>,
     *     expected: string,
     * }>
     */
    public static function ignoresParametersProvider(): \Iterator
    {
        $parameters = ['id' => 'PRJ-4423'];
        yield [
            'scenario' => 'empty string template',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '',
            'parameters' => $parameters,
            'expected' => '',
        ];

        $parameters = ['id' => 'PRJ-4423'];
        yield [
            'scenario' => 'no placeholders',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => 'feat/cunning-plan',
            'parameters' => $parameters,
            'expected' => 'feat/cunning-plan',
        ];

        yield [
            'scenario' => 'no matching placeholder',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => 'feat/{title}',
            'parameters' => $parameters,
            'expected' => 'feat/{title}',
        ];

        $parameters = ['id' => 'PRJ-4423', 'title' => 'cunning-plan'];
        yield [
            'scenario' => 'many parameters, some unmatched',
            'jsonParameters' => (string) json_encode($parameters),
            'template' => '{id}/feat/turnips',
            'parameters' => $parameters,
            'expected' => 'PRJ-4423/feat/turnips',
        ];
    }
}
