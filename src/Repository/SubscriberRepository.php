<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Subscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscriber>
 */
class SubscriberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscriber::class);
    }

    public function findNextUnsentSubscriber(): ?Subscriber
    {
        return $this->createQueryBuilder('s')
            ->where('s.sentAt IS NULL')
            ->andWhere('s.unsubscribedAt IS NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByEmail(string $email): ?Subscriber
    {
        return $this->createQueryBuilder('s')
            ->where('s.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countTotal(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countSent(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.sentAt IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPending(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.sentAt IS NULL')
            ->andWhere('s.unsubscribedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUnsubscribed(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.unsubscribedAt IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countClicked(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.lastClickedAt IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecentActivity(int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.sentAt IS NOT NULL')
            ->orderBy('s.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getPopularLinks(int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.lastClickedLink as link, COUNT(s.id) as count')
            ->where('s.lastClickedLink IS NOT NULL')
            ->groupBy('s.lastClickedLink')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getDashboardStats(): array
    {
        $totalCount = $this->countTotal();
        $sentCount = $this->countSent();
        $pendingCount = $this->countPending();
        $unsubscribedCount = $this->countUnsubscribed();
        $clickedCount = $this->countClicked();

        $sentPercentage = $totalCount > 0 ? round(($sentCount / $totalCount) * 100, 2) : 0;
        $unsubscribeRate = $sentCount > 0 ? round(($unsubscribedCount / $sentCount) * 100, 2) : 0;
        $clickThroughRate = $sentCount > 0 ? round(($clickedCount / $sentCount) * 100, 2) : 0;

        return [
            'total' => $totalCount,
            'sent' => $sentCount,
            'sentPercentage' => $sentPercentage,
            'pending' => $pendingCount,
            'unsubscribed' => $unsubscribedCount,
            'unsubscribeRate' => $unsubscribeRate,
            'clicked' => $clickedCount,
            'clickThroughRate' => $clickThroughRate,
        ];
    }

    public function save(Subscriber $subscriber, bool $flush = false): void
    {
        $this->getEntityManager()->persist($subscriber);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Subscriber $subscriber, bool $flush = false): void
    {
        $this->getEntityManager()->remove($subscriber);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
