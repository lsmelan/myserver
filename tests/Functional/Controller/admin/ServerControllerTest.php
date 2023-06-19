<?php

namespace App\Tests\Functional\Controller\admin;

use App\Client\RedisClient;
use App\Entity\User;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ServerControllerTest extends WebTestCase
{
    private ?RedisClient $redis;
    private ?KernelBrowser $client;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $this->redis = $kernel->getContainer()->get(RedisClient::class);
        $this->client = $kernel->getContainer()->get('test.client');

        $this->createANewUserEntityInTheDatabase();

    }

    public function testFormDisplay(): void
    {
        $this->client->request('GET', '/admin/server/upload-list');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testInvalidFileExtension(): void
    {
        $tempFilePath = 'file.doc';
        $this->generateInvalidFile($tempFilePath);

        $file = new UploadedFile($tempFilePath, $tempFilePath, 'application/msword', null, true);

        $this->client->request('GET', '/admin/server/upload-list');
        $this->client->submitForm('Upload', [
            'form[file]' => $file,
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
            'Only XLSX and CSV files are allowed.',
            $this->client->getResponse()->getContent()
        );

        unlink($tempFilePath);
    }

    /**
     * @dataProvider expectedRedisData
     */
    public function testValidCsvFileUpload(
        string $key,
        array $expectedData,
        string $expectedStorageIndex,
        string $expectedRamIndex,
        string $expectedHddIndex,
        string $expectedLocationIndex
    ): void {
        $tempFilePath = 'file.csv';
        $this->generateCsv($tempFilePath);

        $file = new UploadedFile($tempFilePath, $tempFilePath, 'text/csv', null, true);

        $this->client->request('GET', '/admin/server/upload-list');
        $this->client->submitForm('Upload', [
            'form[file]' => $file,
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
            'File processed successfully.',
            $this->client->getResponse()->getContent()
        );

        $this->assertCount(2, $this->redis->keys('server*'));
        $this->assertSame($expectedData, $this->redis->getAll($key));
        $this->assertNotEmpty($this->redis->keys($expectedStorageIndex));
        $this->assertNotEmpty($this->redis->keys($expectedRamIndex));
        $this->assertNotEmpty($this->redis->keys($expectedHddIndex));
        $this->assertNotEmpty($this->redis->keys($expectedLocationIndex));

        unlink($tempFilePath);
    }

    /**
     * @dataProvider expectedRedisData
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function testValidXlsxFileUpload(
        string $key,
        array $expectedData,
        string $expectedStorageIndex,
        string $expectedRamIndex,
        string $expectedHddIndex,
        string $expectedLocationIndex
    ): void {
        $tempFilePath = 'file.xlsx';
        $this->generateXsl($tempFilePath);

        $file = new UploadedFile(
            $tempFilePath,
            $tempFilePath,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->client->request('GET', '/admin/server/upload-list');
        $this->client->submitForm('Upload', [
            'form[file]' => $file,
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
            'File processed successfully.',
            $this->client->getResponse()->getContent()
        );

        $this->assertCount(2, $this->redis->keys('server*'));
        $this->assertSame($expectedData, $this->redis->getAll($key));
        $this->assertNotEmpty($this->redis->keys($expectedStorageIndex));
        $this->assertNotEmpty($this->redis->keys($expectedRamIndex));
        $this->assertNotEmpty($this->redis->keys($expectedHddIndex));
        $this->assertNotEmpty($this->redis->keys($expectedLocationIndex));

        unlink($tempFilePath);
    }

    private function createANewUserEntityInTheDatabase(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $passwordHasher = $this->client->getContainer()->get('security.user_password_hasher');

        $user = new User();
        $user->setEmail('test_server_'.microtime().'@example.com');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->client->loginUser($user);
    }

    /**
     * @param string $tempFilePath
     * @return void
     */
    private function generateInvalidFile(string $tempFilePath): void
    {
        $fileContent = '';
        file_put_contents($tempFilePath, $fileContent);
    }

    /**
     * @param string $tempFilePath
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function generateXsl(string $tempFilePath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Model');
        $sheet->setCellValue('B1', 'RAM');
        $sheet->setCellValue('C1', 'HDD');
        $sheet->setCellValue('D1', 'Location');
        $sheet->setCellValue('E1', 'Price');

        $sheet->setCellValue('A2', 'Dell R210Intel Xeon X3440');
        $sheet->setCellValue('B2', '16GBDDR3');
        $sheet->setCellValue('C2', '2x2TBSATA2');
        $sheet->setCellValue('D2', 'AmsterdamAMS-01');
        $sheet->setCellValue('E2', '€49.99');

        $sheet->setCellValue('A3', 'HP DL180G62x Intel Xeon E5620');
        $sheet->setCellValue('B3', '32GBDDR3');
        $sheet->setCellValue('C3', '8x2TBSATA2');
        $sheet->setCellValue('D3', 'AmsterdamAMS-01');
        $sheet->setCellValue('E3', '€119.00');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFilePath);
    }

    /**
     * @param string $tempFilePath
     * @return void
     */
    private function generateCsv(string $tempFilePath): void
    {
        $fileContent = 'Model,RAM,HDD,Location,Price' . PHP_EOL;
        $fileContent .= 'Dell R210Intel Xeon X3440,16GBDDR3,2x2TBSATA2,AmsterdamAMS-01,€49.99' . PHP_EOL;
        $fileContent .= 'HP DL180G62x Intel Xeon E5620,32GBDDR3,8x2TBSATA2,AmsterdamAMS-01,€119.00' . PHP_EOL;
        file_put_contents($tempFilePath, $fileContent);
    }

    public function expectedRedisData(): array
    {
        return [
            'case 1' => [
                'server:2',
                [
                    'model' => 'Dell R210Intel Xeon X3440',
                    'ram' => '16GBDDR3',
                    'hdd' => '2x2TBSATA2',
                    'location' => 'AmsterdamAMS-01',
                    'price' => '€49.99',
                ],
                'storage_index:2TB',
                'ram_index:16GB',
                'hdd_index:SATA',
                'location_index:AmsterdamAMS-01',
            ],
            'case 2' =>[
                'server:3',
                [
                    'model' => 'HP DL180G62x Intel Xeon E5620',
                    'ram' => '32GBDDR3',
                    'hdd' => '8x2TBSATA2',
                    'location' => 'AmsterdamAMS-01',
                    'price' => '€119.00',
                ],
                'storage_index:2TB',
                'ram_index:32GB',
                'hdd_index:SATA',
                'location_index:AmsterdamAMS-01',
            ]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->redis = null;
        $this->client = null;
    }
}
