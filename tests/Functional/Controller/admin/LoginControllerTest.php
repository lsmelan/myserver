<?php

namespace App\Tests\Functional\Controller\admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testLoginForm(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="login"]');
    }

    public function testInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/login');
        $client->submitForm('Login', [
            '_username' => 'wrongusername',
            '_password' => 'wrongpassword'
        ]);

        $this->assertTrue($client->getResponse()->isRedirect());

        $client->followRedirect();

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Invalid credentials.', $client->getResponse()->getContent());
    }

    public function testSuccessfulLogin(): void
    {
        $client = static::createClient();

        // Create a new User entity in the database
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $passwordHasher = $client->getContainer()->get('security.user_password_hasher');

        $this->createUser($passwordHasher, $entityManager);

        $client->request('GET', '/admin/login');
        $client->submitForm('Login', [
            '_username' => 'test_login@example.com',
            '_password' => 'password123'
        ]);

        $this->assertTrue($client->getResponse()->isRedirect());

        $client->followRedirect();

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Backoffice', $client->getResponse()->getContent());
    }

    /**
     * @param object|null $passwordHasher
     * @param $entityManager
     * @return void
     */
    private function createUser(?object $passwordHasher, $entityManager): void
    {
        $user = new User();
        $user->setEmail('test_login@example.com');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $entityManager->persist($user);
        $entityManager->flush();
    }
}
