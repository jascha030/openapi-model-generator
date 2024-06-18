<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'generate',
    description: 'Generate Serializable Models from OpenAPI/Swagger documentation.'
)]
class Generate extends Command
{
    public function __construct()
    {
        parent::__construct('generate');
    }

    protected function configure(): void
    {
        $this
            ->addArgument('input-file', InputArgument::REQUIRED, 'path to swagger file.')
            ->addArgument('output-dir', InputArgument::REQUIRED, 'path to output directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputPath = $input->getArgument('input-file');
        $outputPath = $input->getArgument('outpur-dir');

        return Command::SUCCESS;
    }
}
