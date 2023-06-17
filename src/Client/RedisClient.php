<?php

namespace App\Client;

use Predis\Client;

class RedisClient implements CacheInterface
{
    private Client $redis;

    public function __construct(Client $client)
    {
        $this->redis = $client;
    }

    public function hSet(string $key, string $field, string $value): int
    {
        return $this->redis->hset($key, $field, $value);
    }

    public function sAdd(string $key, array $members): int
    {
        return $this->redis->sadd($key, $members);
    }

    public function getAll(string $key): array
    {
        return $this->redis->hgetall($key);
    }

    public function sinter(array|string $keys): array
    {
        return $this->redis->sinter($keys);
    }

    public function keys(string $pattern): array
    {
        return $this->redis->keys($pattern);
    }

    public function flushAll(): void
    {
        $this->redis->flushall();
    }
}
