<?php

namespace App\Tests\Functional\Controller\api;

use App\Client\RedisClient;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServerControllerTest extends WebTestCase
{
    private ?RedisClient $redis;
    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->redis = $kernel->getContainer()->get(RedisClient::class);
        $this->client = $kernel->getContainer()->get('test.client');

        $cacheData = $this->getCacheData();
        $indexes = $this->getIndexes();
        $this->addServers($cacheData, $indexes);
    }

    public function testEmptyDataInRedis(): void
    {
        $this->client->request('GET', '/api/servers', ['filters' => 'non-existing-filter']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($responseData['servers']);
    }

    public function testAttributesInResponse(): void
    {
        $this->client->request('GET', '/api/servers');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData['servers']);
        $this->assertCount(3, $responseData['servers']);

        $this->assertArrayHasKey('model', $responseData['servers'][0]);
        $this->assertArrayHasKey('ram', $responseData['servers'][0]);
        $this->assertArrayHasKey('hdd', $responseData['servers'][0]);
        $this->assertArrayHasKey('location', $responseData['servers'][0]);
        $this->assertArrayHasKey('price', $responseData['servers'][0]);
    }

    public function testFilterByStorage(): void
    {
        $this->client->request('GET', '/api/servers', ['filters' => ['storage_index:480GB']]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData['servers']);
        $this->assertStringContainsString('480GB', $responseData['servers'][0]['hdd']);
    }

    public function testFilterByRAM(): void
    {
        $this->client->request('GET', '/api/servers', ['filters' => ['ram_index:16GB']]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData['servers']);
        $this->assertStringContainsString('16GB', $responseData['servers'][0]['ram']);
    }

    public function testFilterByHDD(): void
    {
        $this->client->request('GET', '/api/servers', ['filters' => ['hdd_index:SSD']]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData['servers']);
        $this->assertStringContainsString('SSD', $responseData['servers'][0]['hdd']);
    }

    public function testFilterByLocation(): void
    {
        $this->client->request('GET', '/api/servers', ['filters' => ['location_index:AmsterdamAMS-01']]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData['servers']);
        $this->assertStringContainsString('AmsterdamAMS-01', $responseData['servers'][0]['location']);
    }

    public function testPagination(): void
    {
        $this->client->request('GET', '/api/servers', ['page' => 1, 'per_page' => 2]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $responseData['servers']);
        $this->assertSame(3, $responseData['total_servers']);
        $this->assertSame(1, $responseData['current_page']);
        $this->assertSame(2, $responseData['per_page']);

        // Next page
        $this->client->request('GET', '/api/servers', ['page' => 2, 'per_page' => 2]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $responseData['servers']);
        $this->assertSame(3, $responseData['total_servers']);
        $this->assertSame(2, $responseData['current_page']);
        $this->assertSame(2, $responseData['per_page']);
    }

    public function testSortingByModel(): void
    {
        // ASC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'model', 'sort_order' => 'asc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('Dell R210Intel Xeon X3440', $responseData['servers'][0]['model']);

        // DESC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'model', 'sort_order' => 'desc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('RH2288v32x Intel Xeon E5-2650V4', $responseData['servers'][0]['model']);
    }

    public function testSortingByRAM(): void
    {
        // ASC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'ram', 'sort_order' => 'asc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('16GBDDR3', $responseData['servers'][0]['ram']);

        // DESC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'ram', 'sort_order' => 'desc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('128GBDDR4', $responseData['servers'][0]['ram']);
    }

    public function testSortingByHDD(): void
    {
        // ASC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'hdd', 'sort_order' => 'asc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('4x480GBSSD', $responseData['servers'][0]['hdd']);

        // DESC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'hdd', 'sort_order' => 'desc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('8x2TBSATA2', $responseData['servers'][0]['hdd']);
    }

    public function testSortingByLocation(): void
    {
        // ASC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'location', 'sort_order' => 'asc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('AmsterdamAMS-01', $responseData['servers'][0]['location']);

        // DESC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'location', 'sort_order' => 'desc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('FrankfurtFRA-10', $responseData['servers'][0]['location']);
    }

    public function testSortingByPrice(): void
    {
        // ASC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'price', 'sort_order' => 'asc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('€49.99', $responseData['servers'][0]['price']);

        // DESC
        $this->client->request('GET', '/api/servers', ['sort_by' => 'price', 'sort_order' => 'desc']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $responseData['servers']);
        $this->assertSame('€227.99', $responseData['servers'][0]['price']);
    }

    private function getIndexes(): array
    {
        return [
            'server:1' => [
                'storage_index:2TB',
                'ram_index:16GB',
                'hdd_index:SATA',
                'location_index:AmsterdamAMS-01'
            ],
            'server:2' => [
                'storage_index:2TB',
                'ram_index:32GB',
                'hdd_index:SATA',
                'location_index:AmsterdamAMS-01'
            ],
            'server:3' => [
                'storage_index:480GB',
                'ram_index:128GB',
                'hdd_index:SSD',
                'location_index:FrankfurtFRA-10'
            ],
        ];
    }

    private function getCacheData(): array
    {
        return [
            'server:1' => [
                'model' => 'Dell R210Intel Xeon X3440',
                'ram' => '16GBDDR3',
                'hdd' => '2x2TBSATA2',
                'location' => 'AmsterdamAMS-01',
                'price' => '€49.99',
            ],
            'server:2' => [
                'model' => 'HP DL180G62x Intel Xeon E5620',
                'ram' => '32GBDDR3',
                'hdd' => '8x2TBSATA2',
                'location' => 'AmsterdamAMS-01',
                'price' => '€119.00',
            ],
            'server:3' => [
                'model' => 'RH2288v32x Intel Xeon E5-2650V4',
                'ram' => '128GBDDR4',
                'hdd' => '4x480GBSSD',
                'location' => 'FrankfurtFRA-10',
                'price' => '€227.99',
            ],
        ];
    }

    private function addServers(array $cacheData, array $indexes): void
    {
        $this->redis->flushAll();

        foreach ($cacheData as $key => $row) {
            foreach ($row as $field => $value) {
                $this->redis->hSet($key, $field, $value);
            }
        }

        foreach ($indexes as $key => $row) {
            foreach ($row as $index) {
                $this->redis->sAdd($index, [$key]);
            }
        }
    }

    protected function tearDown(): void
    {
        $this->redis->flushAll();
        $this->redis = null;
    }
}
