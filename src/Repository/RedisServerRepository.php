<?php

namespace App\Repository;

use App\Client\CacheInterface;
use Exception;

class RedisServerRepository
{
    private const MODEL_COLUMN = 'A';
    private const RAM_COLUMN = 'B';
    private const HDD_COLUMN = 'C';
    private const LOCATION_COLUMN = 'D';
    private const PRICE_COLUMN = 'E';
    private CacheInterface $redisClient;

    public function __construct(CacheInterface $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    public function flushAll(): void
    {
        $this->redisClient->flushAll();
    }

    /**
     * @throws Exception
     */
    public function addServers(string $key, array $data, array $fields): void
    {
        $this->redisClient->hSet($key, strtolower($fields[self::MODEL_COLUMN]), $data[self::MODEL_COLUMN]);
        $this->redisClient->hSet($key, strtolower($fields[self::RAM_COLUMN]), $data[self::RAM_COLUMN]);
        $this->redisClient->hSet($key, strtolower($fields[self::HDD_COLUMN]), $data[self::HDD_COLUMN]);
        $this->redisClient->hSet($key, strtolower($fields[self::LOCATION_COLUMN]), $data[self::LOCATION_COLUMN]);
        $this->redisClient->hSet($key, strtolower($fields[self::PRICE_COLUMN]), $data[self::PRICE_COLUMN]);
        // Create the indexes
        $this->redisClient->sAdd("storage_index:" . $data[self::HDD_COLUMN], [$key]);
        $this->redisClient->sAdd("ram_index:" . $data[self::RAM_COLUMN], [$key]);
        $this->redisClient->sAdd("hdd_index:" . $data[self::HDD_COLUMN], [$key]);
        $this->redisClient->sAdd("location_index:" . $data[self::LOCATION_COLUMN], [$key]);
    }

    public function getServersByFilters(array $keys, string $sortBy, string $sortOrder, int $page, int $perPage): array
    {
        $serverIds = !empty($keys) ? $this->redisClient->sinter($keys) : $this->redisClient->keys('server*');

        $servers = $this->applySorting($serverIds, $sortBy, $sortOrder);

        $totalServers = count($servers);

        $servers = $this->applyPagination($servers, $page, $perPage);

        return [
            'servers' => $servers,
            'totalServers' => $totalServers
        ];
    }

    private function applySorting($serverIds, string $sortBy, string $sortOrder): array
    {
        // Retrieve server data and sort based on the desired field
        $servers = $this->getServersByIds($serverIds);

        usort($servers, function ($a, $b) use ($sortBy, $sortOrder) {
            if ($a[$sortBy] == $b[$sortBy]) {
                return 0;
            }

            if ($sortOrder === 'asc') {
                return ($a[$sortBy] < $b[$sortBy]) ? -1 : 1;
            }

            return ($a[$sortBy] > $b[$sortBy]) ? -1 : 1;
        });

        return $servers;
    }

    private function applyPagination(array $servers, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return array_slice($servers, $offset, $perPage);
    }

    private function getServersByIds(array $serverIds): array
    {
        $servers = [];

        foreach ($serverIds as $serverId) {
            $serverData = $this->redisClient->getAll($serverId);

            if (!empty($serverData)) {
                $servers[] = $serverData;
            }
        }

        return $servers;
    }
}
