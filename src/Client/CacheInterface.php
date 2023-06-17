<?php

namespace App\Client;

interface CacheInterface
{
    public function hSet(string $key, string $field, string $value): int;
    public function sAdd(string $key, array $members): int;
    public function getAll(string $key): array;
    public function sinter(array|string $keys): array;
    public function keys(string $pattern): array;
    public function flushAll(): void;
}
