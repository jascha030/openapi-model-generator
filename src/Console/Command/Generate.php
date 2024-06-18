<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator\Console\Command;

use Jascha030\OpenApiModelGenerator\ClassGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function getcwd;
use function is_dir;

#[AsCommand(
    name: 'generate',
    description: 'Generate Serializable Models from OpenAPI/Swagger documentation.',
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
            ->addOption('namespace', 'ns', InputOption::VALUE_REQUIRED, 'namespace for generated classes.')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'path to output directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputPath = $input->getArgument('input-file');
        $outputPath = $input->getOption('output') ?? getcwd() . '/Model';
        $namespace = $input->getOption('namespace');

        if (!is_dir($outputPath) && !mkdir($outputPath, 0777, true) && !is_dir($outputPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $outputPath));
        }

        $iter = new ClassGenerator($inputPath, $namespace);

        foreach ($iter as $filename => $class) {
            dump($class);
            file_put_contents($outputPath . '/' . $filename, $class);
        }

        return Command::SUCCESS;
    }
}
