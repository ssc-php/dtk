<?php

declare(strict_types=1);

namespace Ssc\Dtk\UserInterface\Cli;

use Ssc\Dtk\Application\TokensSave\TokensSave;
use Ssc\Dtk\Application\TokensSave\TokensSaveHandler;
use Ssc\Dtk\Domain\Exception\ValidationFailedException;
use Ssc\Dtk\Domain\Token\Service;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: TokensSaveCommand::NAME,
    description: TokensSaveCommand::DESCRIPTION,
)]
final class TokensSaveCommand extends Command
{
    public const string NAME = 'tokens:save';

    public const string DESCRIPTION = <<<'TXT'
    Save token to allow DTK to access a service (Github, YouTrack, etc).

    These are stored in the OS keyring (or if not found, in the filesystem).
    TXT;

    /** @var list<string> */
    public const array REQUIRED_OPTIONS = ['service'];

    /** @var array<string, string> name => description */
    public const array ENV_VARS = [
        'DTK_TOKEN' => 'The service token to store (e.g. for Github: Personal Access Token)',
    ];

    public function __construct(
        private readonly TokensSaveHandler $tokensSaveHandler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'service',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Service name (one of: '.Service::toListString().')',
            default: '',
        );
        $this->addOption(
            name: 'interactive',
            mode: InputOption::VALUE_NONE,
            description: 'Prompt for missing options interactively',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $interactive = (bool) $input->getOption('interactive');

        $service = $this->askService($input, $symfonyStyle, $interactive);
        if ('' === $service) {
            $symfonyStyle->error('Missing required option: --service');

            return Command::INVALID;
        }

        $token = $this->askToken($symfonyStyle, $interactive);
        if ('' === $token) {
            $symfonyStyle->error('Missing required env var: '.array_key_first(self::ENV_VARS));

            return Command::INVALID;
        }

        try {
            $this->tokensSaveHandler->handle(new TokensSave(
                $service,
                $token,
            ));
        } catch (ValidationFailedException $validationFailedException) {
            $symfonyStyle->error($validationFailedException->getMessage());

            return Command::INVALID;
        }

        $symfonyStyle->success('Token saved');

        return Command::SUCCESS;
    }

    private function askService(InputInterface $input, SymfonyStyle $symfonyStyle, bool $interactive): string
    {
        $rawService = $input->getOption('service');
        $service = \is_string($rawService) ? $rawService : '';
        if ('' !== $service || !$interactive) {
            return $service;
        }

        $choices = Service::toArray();
        $chosen = $symfonyStyle->choice('service', $choices);

        return \is_string($chosen) ? $chosen : $choices[0];
    }

    private function askToken(SymfonyStyle $symfonyStyle, bool $interactive): string
    {
        $token = getenv(array_key_first(self::ENV_VARS)) ?: '';
        if ('' !== $token || !$interactive) {
            return $token;
        }

        $entered = $symfonyStyle->askHidden('token');

        return \is_string($entered) ? $entered : '';
    }
}
