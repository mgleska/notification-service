<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\OutboxEntity;
use App\Domain\Enum\DeliveryStatusEnum;
use App\Domain\Repository\OutboxRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OutboxEntity>
 */
class OutboxRepository extends ServiceEntityRepository implements OutboxRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OutboxEntity::class);
    }

    public function save(OutboxEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function advanceTryNumber(int $id): void
    {
        $entity = $this->find($id);
        $entity->setTryNumber($entity->getTryNumber() + 1);
        $this->save($entity, true);
    }

    public function registerDelivery(int $id): void
    {
        $entity = $this->find($id);
        $entity->setDeliveryStatus(DeliveryStatusEnum::DONE);
        $entity->setDeliveredAt(new DateTimeImmutable());
        $this->save($entity, true);
    }
}
