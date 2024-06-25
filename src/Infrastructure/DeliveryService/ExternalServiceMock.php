<?php

declare(strict_types=1);

namespace App\Infrastructure\DeliveryService;

use Exception;

class ExternalServiceMock
{
    /**
     * @throws Exception
     */
    public function process(): void
    {
        // simulated processing by external delivery service
        sleep(2 + rand(0, 1));
        $success = (rand(1, 10) <= 5);
        if (!$success) {
            throw new Exception('delivery failed');
        }
    }
}
