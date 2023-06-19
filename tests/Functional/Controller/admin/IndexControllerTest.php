<?php

namespace App\Tests\Functional\Controller\admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    public function testIndexPage(): void
    {
        $client = static::createClient();

        $this->createANewUserEntityInTheDatabase($client);

        $client->request('GET', '/admin');

        $this->assertStringContainsString('Redirecting', $client->getResponse()->getContent());
    }

    public function testRedirectToLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin');

        $this->assertStringContainsString('Redirecting', $client->getResponse()->getContent());
    }

    private function createANewUserEntityInTheDatabase(KernelBrowser $client): void
    {
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $passwordHasher = $client->getContainer()->get('security.user_password_hasher');

        $user = new User();
        $user->setEmail('test_index@example.com');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);
    }
}
