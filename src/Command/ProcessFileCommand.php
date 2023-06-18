<?php

namespace App\Command;

use App\Repository\RedisServerRepository;
use App\Service\SpreadsheetService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessFileCommand extends Command
{
    private const ARGUMENT = 'file';
    private const LAST_COLUMN = 'E';
    private const HEADER = 1;
    private SpreadsheetService $spreadsheetService;
    private RedisServerRepository $redisServerRepository;

    public function __construct(
        SpreadsheetService $spreadsheetService,
        RedisServerRepository $redisServerRepository,
        string $name = null
    ) {
        $this->spreadsheetService = $spreadsheetService;
        $this->redisServerRepository = $redisServerRepository;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('app:process-file')
            ->setDescription('Process a list of servers')
            ->addArgument(self::ARGUMENT, InputArgument::REQUIRED, 'Path to the XLSX file');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument(self::ARGUMENT);

        try {
            $rows = $this->spreadsheetService->getRange($filePath, self::LAST_COLUMN);
            if ($rows) {
                $this->redisServerRepository->flushAll();
                $fields = $rows[self::HEADER];
                unset($rows[self::HEADER]);

                foreach ($rows as $key => $data) {
                    // Cache
                    $this->redisServerRepository->addServers('server:'.$key, $data, $fields);
                }
            }
        } catch (\Exception $e) {
            throw new $e();
        }

        return Command::SUCCESS;
    }
}
