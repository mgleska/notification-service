<?php

declare(strict_types=1);

namespace App\Infrastructure\DeliveryService;

use App\Domain\Dto\DeliverByServiceDto;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AlwaysFailService
{
    private string $serviceName;

    public function __construct()
    {
        $this->serviceName = preg_replace('/.*\\\\/', '', static::class);
    }

    public function __invoke(DeliverByServiceDto $message): ?bool
    {
        if ($message->serviceName !== $this->serviceName) {
            return null;
        }

        return false;
    }
}
