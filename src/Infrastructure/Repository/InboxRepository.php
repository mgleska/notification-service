<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\InboxEntity;
use App\Domain\Repository\InboxRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InboxEntity>
 */
class InboxRepository extends ServiceEntityRepository implements InboxRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboxEntity::class);
    }

    public function save(InboxEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
