<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'app:hello'
)]
class HelloWorld extends Command
{
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln("Hello, world!");
    return Command::SUCCESS;
  }
}
