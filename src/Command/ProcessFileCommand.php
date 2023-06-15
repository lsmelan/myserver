<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessFileCommand extends Command
{
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
            foreach ($this->getRows($filePath, 200) as $row) {
                file_put_contents('/tmp/test.log', var_export($row, true), FILE_APPEND);
                var_dump($row);
            }
        } catch (Exception $e) {
            throw new $e;
        }

        return Command::SUCCESS;
    }

    private function getRows(string $filePath, int $chunkSize = 1000): \Generator
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        for ($row = 1; $row <= $highestRow; $row += $chunkSize) {
            $endRow = $row + $chunkSize - 1;
            if ($endRow > $highestRow) {
                $endRow = $highestRow;
            }

            $range = 'A' . $row . ':' . $highestColumn . $endRow;
            yield $worksheet->rangeToArray($range, returnCellRef: true);
        }
    }
}
