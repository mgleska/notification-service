<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\InboxEntity;

interface InboxRepositoryInterface
{
    public function save(InboxEntity $entity, bool $flush = false): void;
}
