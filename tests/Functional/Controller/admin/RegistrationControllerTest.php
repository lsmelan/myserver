<?php

namespace App\Tests\Functional\Controller\admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->client = $kernel->getContainer()->get('test.client');
    }

    public function testRegister(): void
    {
        $this->client->request('GET', '/admin/register');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->submitForm('Register', [
            'registration_form[email]' => 'test@example.com',
            'registration_form[agreeTerms]' => '1',
            'registration_form[plainPassword]' => 'password123',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Login', $this->client->getResponse()->getContent());

        // Assert that the user is persisted in the database
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testInvalidAccount(): void
    {
        $passwordHasher = $this->client->getContainer()->get('security.user_password_hasher');

        $this->createUser($passwordHasher);

        $this->client->request('GET', '/admin/register');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->client->submitForm('Register', [
            'registration_form[email]' => 'test_existing_account@example.com',
            'registration_form[agreeTerms]' => '1',
            'registration_form[plainPassword]' => 'password123',
        ]);

        $this->assertFalse($this->client->getResponse()->isRedirect());

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
            'There is already an account with this email',
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @param object|null $passwordHasher
     * @return void
     */
    private function createUser(?object $passwordHasher): void
    {
        $user = new User();
        $user->setEmail('test_existing_account@example.com');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager = null;
        $this->client = null;
    }
}
