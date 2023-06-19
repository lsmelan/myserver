<?php

namespace App\Repository;

use App\Client\CacheInterface;

class RedisServerRepository
{
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
     * @throws \Exception
     */
    public function addServers(string $key, array $toCache, array $indexes): void
    {
        foreach ($toCache as $field => $value) {
            $this->redisClient->hSet($key, $field, $value);
        }

        foreach ($indexes as $index) {
            $this->redisClient->sAdd($index, [$key]);
        }
    }

    public function getServersByFilters(array $keys, string $sortBy, string $sortOrder, int $page, int $perPage): array
    {
        $serverIds = !empty($keys) ? $this->redisClient->sinter($keys) : $this->redisClient->keys('server*');

        $servers = $this->applySorting($serverIds, $sortBy, $sortOrder);

        $totalServers = count($servers);

        $servers = $this->applyPagination($servers, $page, $perPage);

        return [
            'servers' => $servers,
            'totalServers' => $totalServers,
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

            if ('asc' === $sortOrder) {
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
