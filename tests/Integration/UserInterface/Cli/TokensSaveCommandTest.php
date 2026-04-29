<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Integration\UserInterface\Cli;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\ServiceFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Token\TokenFixture;
use Ssc\Dtk\Tests\Infrastructure\TestKernelSingleton;
use Ssc\Dtk\UserInterface\Cli\TokensSaveCommand;
use Symfony\Component\Console\Command\Command;

#[CoversNothing]
#[Medium]
final class TokensSaveCommandTest extends TestCase
{
    public function test_it_runs_command_successfully(): void
    {
        $application = TestKernelSingleton::get()->application();
        putenv('DTK_TOKEN='.TokenFixture::makeString());

        $application->run([
            'command' => TokensSaveCommand::NAME,
            '--service' => ServiceFixture::makeString(),
        ]);

        putenv('DTK_TOKEN');
        $this->assertSame(Command::SUCCESS, $application->getStatusCode());
    }

    /**
     * @param array<string, string> $input
     */
    #[DataProvider('optionsProvider')]
    #[TestDox('It has option: $scenario')]
    public function test_it_has_options(
        string $scenario,
        array $input,
    ): void {
        $application = TestKernelSingleton::get()->application();
        putenv('DTK_TOKEN='.TokenFixture::makeString());

        $application->run($input);

        putenv('DTK_TOKEN');
        $this->assertSame(Command::SUCCESS, $application->getStatusCode());
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     input: array<string, string>,
     * }>
     */
    public static function optionsProvider(): \Iterator
    {
        yield [
            'scenario' => '--service',
            'input' => [
                'command' => TokensSaveCommand::NAME,
                '--service' => ServiceFixture::makeString(),
            ],
        ];
    }

    /**
     * @param array<string, string> $envVars
     */
    #[DataProvider('envVarsProvider')]
    #[TestDox('It has env var: $scenario')]
    public function test_it_has_env_vars(
        string $scenario,
        array $envVars,
    ): void {
        $application = TestKernelSingleton::get()->application();

        foreach ($envVars as $key => $value) {
            putenv("{$key}={$value}");
        }

        $application->run([
            'command' => TokensSaveCommand::NAME,
            '--service' => ServiceFixture::makeString(),
        ]);

        foreach (array_keys($envVars) as $key) {
            putenv($key);
        }

        $this->assertSame(Command::SUCCESS, $application->getStatusCode());
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     envVars: array<string, string>,
     * }>
     */
    public static function envVarsProvider(): \Iterator
    {
        yield [
            'scenario' => 'DTK_TOKEN',
            'envVars' => ['DTK_TOKEN' => TokenFixture::makeString()],
        ];
    }

    /**
     * @param array<string, string|bool> $input
     * @param list<string>               $interractiveInputs
     * @param array<string, string>      $envVars
     */
    #[DataProvider('promptsProvider')]
    #[TestDox('It prompts for missing value with --interactive: $scenario')]
    public function test_it_prompts_for_missing_value_with_interactive(
        string $scenario,
        array $input,
        array $interractiveInputs,
        array $envVars = [],
    ): void {
        $application = TestKernelSingleton::get()->application();

        foreach ($envVars as $key => $value) {
            putenv("{$key}={$value}");
        }

        $application->setInputs($interractiveInputs);

        $application->run($input);

        $application->setInputs([]);
        foreach (array_keys($envVars) as $key) {
            putenv($key);
        }

        $this->assertSame(Command::SUCCESS, $application->getStatusCode());
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     input: array<string, string|bool>,
     *     interractiveInputs: list<string>,
     *     envVars?: array<string, string>,
     * }>
     */
    public static function promptsProvider(): \Iterator
    {
        yield [
            'scenario' => '--service',
            'input' => [
                'command' => TokensSaveCommand::NAME,
                '--interactive' => true,
            ],
            'interractiveInputs' => [ServiceFixture::makeString()],
            'envVars' => ['DTK_TOKEN' => TokenFixture::makeString()],
        ];
        yield [
            'scenario' => 'DTK_TOKEN',
            'input' => [
                'command' => TokensSaveCommand::NAME,
                '--service' => ServiceFixture::makeString(),
                '--interactive' => true,
            ],
            'interractiveInputs' => [TokenFixture::makeString()],
        ];
    }

    /**
     * @param array<string, string> $input
     * @param array<string, string> $envVars
     */
    #[DataProvider('missingRequiredOptionProvider')]
    #[TestDox('It fails on missing required option: $scenario')]
    public function test_it_fails_on_missing_required_option(
        string $scenario,
        array $input,
        array $envVars = [],
    ): void {
        $application = TestKernelSingleton::get()->application();

        foreach ($envVars as $key => $value) {
            putenv("{$key}={$value}");
        }

        $application->run($input);

        foreach (array_keys($envVars) as $key) {
            putenv($key);
        }

        $this->assertSame(Command::INVALID, $application->getStatusCode());
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     input: array<string, string>,
     *     envVars?: array<string, string>,
     * }>
     */
    public static function missingRequiredOptionProvider(): \Iterator
    {
        yield [
            'scenario' => '--service',
            'input' => [
                'command' => TokensSaveCommand::NAME,
            ],
            'envVars' => ['DTK_TOKEN' => TokenFixture::makeString()],
        ];
        yield [
            'scenario' => 'DTK_TOKEN',
            'input' => [
                'command' => TokensSaveCommand::NAME,
                '--service' => ServiceFixture::makeString(),
            ],
        ];
    }

    /**
     * @param array<string, string> $input
     * @param array<string, string> $envVars
     */
    #[DataProvider('invalidInputProvider')]
    #[TestDox('It fails on invalid input: $scenario')]
    public function test_it_fails_on_invalid_input(
        string $scenario,
        array $input,
        array $envVars = [],
    ): void {
        $application = TestKernelSingleton::get()->application();

        foreach ($envVars as $key => $value) {
            putenv("{$key}={$value}");
        }

        $application->run($input);

        foreach (array_keys($envVars) as $key) {
            putenv($key);
        }

        $this->assertSame(Command::INVALID, $application->getStatusCode());
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     input: array<string, string>,
     *     envVars?: array<string, string>,
     * }>
     */
    public static function invalidInputProvider(): \Iterator
    {
        yield [
            'scenario' => '--service',
            'input' => [
                'command' => TokensSaveCommand::NAME,
                '--service' => 'invalid',
            ],
            'envVars' => ['DTK_TOKEN' => TokenFixture::makeString()],
        ];
    }
}
