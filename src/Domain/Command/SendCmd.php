<?php

declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Dto\OutboxScheduleDto;
use App\Domain\Dto\SendDto;
use App\Domain\Entity\InboxEntity;
use App\Domain\Entity\OutboxEntity;
use App\Domain\Enum\DeliveryStatusEnum;
use App\Domain\Repository\InboxRepositoryInterface;
use App\Domain\Repository\OutboxRepositoryInterface;
use App\Domain\Support\ChannelsConfig;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;

class SendCmd
{
    public function __construct(
        private readonly InboxRepositoryInterface $inboxRepository,
        private readonly OutboxRepositoryInterface $outboxRepository,
        private readonly ChannelsConfig $channelsConfig,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws Exception
     */
    public function send(SendDto $dto): int
    {
        $channels = $this->channelsConfig->parseChannels();

        $inbox = new InboxEntity();
        $inbox->setUserId($dto->userId);
        $inbox->setEmail($dto->email);
        $inbox->setPhoneNumber($dto->phoneNumber);
        $inbox->setPushToken($dto->pushToken);
        $inbox->setMessage($dto->message);
        $inbox->setCreatedAt(new DateTimeImmutable());

        try {
            $this->entityManager->beginTransaction();
            $this->inboxRepository->save($inbox, true);

            $outboxIds = [];
            foreach ($channels as $channel) {
                $outbox = new OutboxEntity();
                $outbox->setInboxId($inbox->getId());
                $outbox->setChannel($channel);
                $outbox->setDeliveryStatus(DeliveryStatusEnum::WAITING);
                $outbox->setTryNumber(0);

                $this->outboxRepository->save($outbox, true);
                $outboxIds[] = $outbox->getId();
            }
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        foreach ($outboxIds as $id) {
            $message = new OutboxScheduleDto($id);
            $this->bus->dispatch($message);
        }

        return $inbox->getId();
    }
}
