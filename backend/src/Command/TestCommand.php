<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:testing:test')]
class TestCommand extends Command
{
    protected function configure()
    {
        $this->addOption("na","na",InputOption::VALUE_REQUIRED, "Test");

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tets = $input->getOption("na");
        $output->writeln("Hello world ".$tets );

       return Command::SUCCESS;
    }

}