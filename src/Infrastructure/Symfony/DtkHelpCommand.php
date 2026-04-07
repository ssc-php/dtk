<?php

declare(strict_types=1);

namespace Ssc\Dtk\Infrastructure\Symfony;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A Symfony HelpCommand, with DTK custom descriptor.
 * Delegates rendering to DtkCommandDescriptor instead of the default TextDescriptor.
 */
final class DtkHelpCommand extends HelpCommand
{
    private ?Command $commandToDescribe = null;

    public function __construct(
        private readonly DtkCommandDescriptor $descriptor,
        private readonly Application $cli,
    ) {
        parent::__construct();
    }

    /**
     * Symfony calls this to pass the command to describe.
     *
     * e.g. `tokens:save --help` passes `tokens:save` here.
     */
    #[\Override]
    public function setCommand(Command $command): void
    {
        parent::setCommand($command);

        $this->commandToDescribe = $command;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->commandToDescribe;
        $this->commandToDescribe = null;
        if (!$command instanceof Command) {
            $commandName = $input->getArgument('command_name');
            $command = $this->cli->find(\is_string($commandName) ? $commandName : 'help');
        }

        $descriptorHelper = new DescriptorHelper();
        $descriptorHelper->register('txt', $this->descriptor);
        $descriptorHelper->describe($output, $command, [
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
        ]);

        return Command::SUCCESS;
    }
}
