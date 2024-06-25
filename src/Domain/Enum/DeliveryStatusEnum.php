<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum DeliveryStatusEnum: string
{
    case WAITING = 'waiting';
    case DONE = 'done';
}
