<?php

declare(strict_types=1);

namespace App\Infrastructure\DeliveryService;

use App\Domain\Dto\DeliverByServiceDto;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function is_null;

#[AsMessageHandler]
class BackupSmsService
{
    private string $serviceName;

    public function __construct(
        public readonly LoggerInterface $logger,
        public readonly ExternalServiceMock $serviceMock,
    ) {
        $this->serviceName = preg_replace('/.*\\\\/', '', static::class);
    }

    /**
     * @throws Exception
     */
    public function __invoke(DeliverByServiceDto $message): ?bool
    {
        if ($message->serviceName !== $this->serviceName) {
            return null;
        }

        if (is_null($message->phoneNumber)) {
            $this->logger->warning('delivery service {service}: required parameter "phoneNumber" is NULL', ['service' => $this->serviceName]);
            return false;
        }

        $this->logger->info(
            'delivery service {service}: phoneNumber={phoneNumber}: processing start',
            ['service' => $this->serviceName, 'phoneNumber' => $message->phoneNumber]
        );

        try {
            $this->serviceMock->process();
        } catch (Exception) {
            $this->logger->error(
                'delivery service {service}: phoneNumber={phoneNumber}: processing failed',
                ['service' => $this->serviceName, 'phoneNumber' => $message->phoneNumber]
            );
            return false;
        }

        $this->logger->notice( // notice level is too high - it is used only for demo purpose
            'delivery service {service}: phoneNumber={phoneNumber}: processing finished with success',
            ['service' => $this->serviceName, 'phoneNumber' => $message->phoneNumber]
        );

        return true;
    }
}
