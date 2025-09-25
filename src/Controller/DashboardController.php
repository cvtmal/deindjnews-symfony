<?php

namespace App\Controller;

use App\Repository\SubscriberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DashboardController extends AbstractController
{
    public function __construct(
        private SubscriberRepository $subscriberRepository,
        private CacheInterface $cache
    ) {}

    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        $stats = $this->cache->get('dashboard_stats', function (ItemInterface $item) {
            $item->expiresAfter(300); // 5 minutes cache
            return $this->subscriberRepository->getDashboardStats();
        });

        $recentActivity = $this->cache->get('dashboard_recent_activity', function (ItemInterface $item) {
            $item->expiresAfter(300);
            return $this->subscriberRepository->findRecentActivity(50);
        });

        $popularLinks = $this->cache->get('dashboard_popular_links', function (ItemInterface $item) {
            $item->expiresAfter(300);
            return $this->subscriberRepository->getPopularLinks(5);
        });

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'popularLinks' => $popularLinks,
        ]);
    }
}