<?php

namespace App\Controller\Api;

use App\Repository\RedisServerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ServerController extends AbstractController
{
    private RedisServerRepository $redisServerRepository;

    public function __construct(RedisServerRepository $redisServerRepository)
    {
        $this->redisServerRepository = $redisServerRepository;
    }

    #[Route('/servers', name: 'servers_list', methods: ['GET'])]
    public function listServers(Request $request): JsonResponse
    {
        // Retrieve the request parameters
        $filters = $request->get('filters', []);
        $sortBy = $request->query->get('sort_by', 'model');
        $sortOrder = $request->query->get('sort_order', 'asc');
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('per_page', 10);

        // Retrieve the list of servers from your data source
        $servers = $this->getServersFromRedis((array) $filters, $sortBy, $sortOrder, $page, $perPage);

        // Transform the servers array into a JSON response
        return new JsonResponse($servers);
    }

    private function getServersFromRedis(array $filters, string $sortBy, string $sortOrder, int $page, int $perPage): array
    {
        $servers = $this->redisServerRepository->getServersByFilters(
            $filters,
            strtolower($sortBy),
            $sortOrder,
            $page,
            $perPage
        );

        return [
            'servers' => $servers['servers'],
            'total_servers' => $servers['totalServers'],
            'current_page' => $page,
            'per_page' => $perPage,
        ];
    }
}
