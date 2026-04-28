<?php

declare(strict_types=1);

namespace Ssc\Dtk\Tests\Integration\UserInterface\Cli;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\BranchNameFixture;
use Ssc\Dtk\Tests\Fixtures\Domain\Git\StartingPointFixture;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Mktemp;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Filesystem\Rmdir;
use Ssc\Dtk\Tests\Fixtures\Infrastructure\Git\InitGitRepo;
use Ssc\Dtk\Tests\Infrastructure\TestKernelSingleton;
use Ssc\Dtk\UserInterface\Cli\WorkStartCommand;
use Symfony\Component\Console\Command\Command;

#[CoversNothing]
#[Medium]
final class WorkStartCommandTest extends TestCase
{
    public function test_it_runs_command_successfully(): void
    {
        $repoDir = Mktemp::run();

        try {
            InitGitRepo::run($repoDir);

            $application = TestKernelSingleton::get()->application();
            $application->run([
                'command' => WorkStartCommand::NAME,
                '--new-branch' => BranchNameFixture::makeString(),
                '--starting-point' => StartingPointFixture::makeString(),
            ]);

            $this->assertSame(Command::SUCCESS, $application->getStatusCode());
        } finally {
            Rmdir::run($repoDir);
        }
    }

    /**
     * @param array<string, string|bool> $input
     */
    #[DataProvider('optionsProvider')]
    #[TestDox('It has option: $scenario')]
    public function test_it_has_options(
        string $scenario,
        array $input,
    ): void {
        $repoDir = Mktemp::run();

        try {
            InitGitRepo::run($repoDir);

            $application = TestKernelSingleton::get()->application();
            $application->run($input);

            $this->assertSame(Command::SUCCESS, $application->getStatusCode());
        } finally {
            Rmdir::run($repoDir);
        }
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     input: array<string, string|bool>,
     * }>
     */
    public static function optionsProvider(): \Iterator
    {
        yield [
            'scenario' => '--new-branch',
            'input' => [
                'command' => WorkStartCommand::NAME,
                '--new-branch' => BranchNameFixture::makeString(),
                '--starting-point' => StartingPointFixture::makeString(),
            ],
        ];
        yield [
            'scenario' => '--starting-point',
            'input' => [
                'command' => WorkStartCommand::NAME,
                '--new-branch' => BranchNameFixture::makeString(),
                '--starting-point' => StartingPointFixture::makeString(),
            ],
        ];
        yield [
            'scenario' => '--ticket-id',
            'input' => [
                'command' => WorkStartCommand::NAME,
                '--new-branch' => BranchNameFixture::makeString(),
                '--starting-point' => StartingPointFixture::makeString(),
                '--ticket-id' => 'PRJ-4423',
            ],
        ];
        yield [
            'scenario' => '--autostash',
            'input' => [
                'command' => WorkStartCommand::NAME,
                '--new-branch' => BranchNameFixture::makeString(),
                '--starting-point' => StartingPointFixture::makeString(),
                '--autostash' => true,
            ],
        ];
    }

    /**
     * @param array<string, string> $input
     * @param list<string>          $interactiveInputs
     */
    #[DataProvider('promptsProvider')]
    #[TestDox('It asks for missing option value: $scenario')]
    public function test_it_asks_for_missing_option_value(
        string $scenario,
        array $input,
        array $interactiveInputs,
    ): void {
        $repoDir = Mktemp::run();

        try {
            InitGitRepo::run($repoDir);

            $application = TestKernelSingleton::get()->application();
            $application->setInputs($interactiveInputs);

            $application->run($input);

            $application->setInputs([]);
            $this->assertSame(Command::SUCCESS, $application->getStatusCode());
        } finally {
            Rmdir::run($repoDir);
        }
    }

    /**
     * @return \Iterator<array{
     *     scenario: string,
     *     input: array<string, string>,
     *     interactiveInputs: list<string>,
     * }>
     */
    public static function promptsProvider(): \Iterator
    {
        yield [
            'scenario' => '--new-branch',
            'input' => [
                'command' => WorkStartCommand::NAME,
                '--starting-point' => StartingPointFixture::makeString(),
            ],
            'interactiveInputs' => [BranchNameFixture::makeString()],
        ];
    }
}
