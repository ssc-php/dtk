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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $rawService = $input->getOption('service');
        $service = \is_string($rawService) ? $rawService : '';
        if ('' === $service) {
            $choices = Service::toArray();
            $chosen = $symfonyStyle->choice('service', $choices);
            $service = \is_string($chosen) ? $chosen : $choices[0];
        }

        $token = getenv(array_key_first(self::ENV_VARS)) ?: '';
        if ('' === $token) {
            $entered = $symfonyStyle->askHidden('token');
            $token = \is_string($entered) ? $entered : '';
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
}
