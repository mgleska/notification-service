<?php

declare(strict_types=1);

namespace App\Domain\Dto;

class OutboxScheduleDto
{
    public readonly int $outboxId;

    public function __construct(int $outboxId)
    {
        $this->outboxId = $outboxId;
    }
}
