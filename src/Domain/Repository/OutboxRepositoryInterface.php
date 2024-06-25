<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\OutboxEntity;

interface OutboxRepositoryInterface
{
    public function save(OutboxEntity $entity, bool $flush = false): void;

    public function advanceTryNumber(int $id): void;

    public function registerDelivery(int $id): void;
}
