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
    private const MODEL_COLUMN = 'A';
    private const RAM_COLUMN = 'B';
    private const HDD_COLUMN = 'C';
    private const LOCATION_COLUMN = 'D';
    private const PRICE_COLUMN = 'E';
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
                    $toCache = $this->getDataToCache($fields, $data);
                    $indexes = $this->getIndexes($data);
                    $this->redisServerRepository->addServers($this->getKey($key), $toCache, $indexes);
                }
            }
        } catch (\Exception $e) {
            throw new $e();
        }

        return Command::SUCCESS;
    }

    private function getKey(int|string $key): string
    {
        return 'server:' . $key;
    }

    private function getDataToCache(mixed $fields, mixed $data): array
    {
        return [
            strtolower($fields[self::MODEL_COLUMN]) => $data[self::MODEL_COLUMN],
            strtolower($fields[self::RAM_COLUMN]) => $data[self::RAM_COLUMN],
            strtolower($fields[self::HDD_COLUMN]) => $data[self::HDD_COLUMN],
            strtolower($fields[self::LOCATION_COLUMN]) => $data[self::LOCATION_COLUMN],
            strtolower($fields[self::PRICE_COLUMN]) => $data[self::PRICE_COLUMN],
        ];
    }

    private function getIndexes(mixed $data): array
    {
        preg_match('/([0-9]{1,3}[GT]B)/', $data[self::HDD_COLUMN], $storage);
        preg_match('/([0-9]{1,2}GB)/', $data[self::RAM_COLUMN], $ram);
        preg_match('/(SATA|SAS|SSD)/', $data[self::HDD_COLUMN], $hdd);

        return [
            'storage_index:' . ($storage[1] ?? ''),
            'ram_index:' . ($ram[1] ?? ''),
            'hdd_index:' . ($hdd[1] ?? ''),
            'location_index:' . $data[self::LOCATION_COLUMN]
        ];
    }
}
