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

    public function getServersByFilters(
        array $filters,
        array $filtersOr,
        string $sortBy,
        string $sortOrder,
        int $page,
        int $perPage
    ): array {
        $serverIds = [];
        if (empty($filters) && empty($filtersOr)) {
            $serverIds = $this->redisClient->keys('server*');
        } else {
            $serverIds = $this->getServerIds($filters, $filtersOr, $serverIds);
        }

        $servers = $this->applySorting($serverIds, $sortBy, $sortOrder);

        $totalServers = count($servers);

        $servers = $this->applyPagination($servers, $page, $perPage);

        return [
            'servers' => $servers,
            'totalServers' => $totalServers,
        ];
    }

    private function extractTermForSorting(string $string, string $sortBy): string
    {
        return match ($sortBy) {
            'ram' => preg_replace('/(?:GB|DDR\d+)/', '', $string),
            'hdd' => preg_replace('/\d+x\d+TB([A-Z]+)/', '$1', $string),
            'price' => preg_replace('/[^\d]+/', '', $string),
            default => $string
        };
    }

    private function applySorting($serverIds, string $sortBy, string $sortOrder): array
    {
        // Retrieve server data and sort based on the desired field
        $servers = $this->getServersByIds($serverIds);

        usort($servers, function ($a, $b) use ($sortBy, $sortOrder) {
            $string1 = $this->extractTermForSorting($a[$sortBy], $sortBy);
            $string2 = $this->extractTermForSorting($b[$sortBy], $sortBy);

            if ($string1 == $string2) {
                return 0;
            }

            if ('asc' === $sortOrder) {
                return ($string1 < $string2) ? -1 : 1;
            }

            return ($string1 > $string2) ? -1 : 1;
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

    private function getServerIds(array $filters, array $filtersOr, array $serverIds): array
    {
        if (!empty($filters)) {
            $serverIds = $this->redisClient->sinter($filters);
        }

        if (!empty($filtersOr)) {
            $serverIdsOr = $this->redisClient->sunion($filtersOr);
            $serverIds = !empty($filters) ? array_intersect($serverIds, $serverIdsOr) : $serverIdsOr;
        }
        return $serverIds;
    }
}
