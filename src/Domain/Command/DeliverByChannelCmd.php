<?php

declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Dto\DeliverByChannelDto;
use App\Domain\Dto\DeliverByServiceDto;
use App\Domain\Repository\OutboxRepositoryInterface;
use App\Domain\Support\ChannelsConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class DeliverByChannelCmd
{
    public function __construct(
        private readonly ChannelsConfig $channelsConfig,
        private readonly OutboxRepositoryInterface $outboxRepository,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function deliverByChannel(DeliverByChannelDto $dto): bool
    {
        $services = $this->channelsConfig->getDeliveryServices($dto->channel);
        $this->outboxRepository->advanceTryNumber($dto->outboxId);

        foreach ($services as $service) {
            $serviceDto = new DeliverByServiceDto(
                $service,
                $dto->message,
                $dto->email,
                $dto->phoneNumber,
                $dto->pushToken,
            );

            $envelope = $this->bus->dispatch($serviceDto);
            $handledStamps = $envelope->all(HandledStamp::class);

            $success = false;
            foreach ($handledStamps as $stamp) {
                if ($stamp->getResult() === true) {
                    $success = true;
                    break;
                }
            }
            if ($success) {
                $this->outboxRepository->registerDelivery($dto->outboxId);
                $this->logger->info(
                    '{command}: channel={channel}: Message successfully delivered by service {service}',
                    ['command' => 'deliverByChannel', 'channel' => $dto->channel, 'service' => $service]
                );
                return true;
            }

            $this->logger->warning(
                '{command}: channel={channel}: Failed delivery by service {service}',
                ['command' => 'deliverByChannel', 'channel' => $dto->channel, 'service' => $service]
            );
        }

        return false;
    }
}
