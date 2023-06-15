<?php

namespace App\Command;

use App\Service\SpreadsheetService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessFileCommand extends Command
{
    private SpreadsheetService $spreadsheetService;

    public function __construct(SpreadsheetService $service, string $name = null)
    {
        $this->spreadsheetService = $service;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('app:process-file')
            ->setDescription('Process a list of servers asynchronously')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the XLSX file');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('file');

        // Process the file in chunks
        try {
            foreach ($this->spreadsheetService->getRows($filePath, 200) as $row) {
                file_put_contents('/tmp/test.log', var_export($row, true), FILE_APPEND);
                var_dump($row);
            }
        } catch (Exception $e) {
            throw new $e;
        }

        return Command::SUCCESS;
    }
}
