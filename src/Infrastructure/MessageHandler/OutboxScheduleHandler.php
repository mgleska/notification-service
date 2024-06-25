<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Command\DeliverByChannelCmd;
use App\Domain\Dto\DeliverByChannelDto;
use App\Domain\Dto\OutboxScheduleDto;
use App\Infrastructure\Repository\InboxRepository;
use App\Infrastructure\Repository\OutboxRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OutboxScheduleHandler
{
    public function __construct(
        private readonly OutboxRepository $outboxRepository,
        private readonly InboxRepository $inboxRepository,
        private readonly DeliverByChannelCmd $deliverByChannelCmd,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(OutboxScheduleDto $message): void
    {
        $this->logger->notice( // notice level is too high - it is used only for demo purpose
            'OutboxScheduleHandler: outboxId={id}: processing start',
            ['id' => $message->outboxId]
        );

        $outbox = $this->outboxRepository->find($message->outboxId);
        $inbox = $this->inboxRepository->find($outbox->getInboxId());

        $dto = new DeliverByChannelDto(
            $outbox->getChannel(),
            $outbox->getId(),
            $inbox->getMessage(),
            $inbox->getEmail(),
            $inbox->getPhoneNumber(),
            $inbox->getPushToken()
        );
        $success = $this->deliverByChannelCmd->deliverByChannel($dto);

        if (! $success) {
            $this->logger->warning(
                'OutboxScheduleHandler: outboxId={id}: processing failed - will re-schedule',
                ['id' => $message->outboxId]
            );
            throw new Exception('trigger re-schedule');
        }

        $this->logger->notice( // notice level is too high - it is used only for demo purpose
            'OutboxScheduleHandler: outboxId={id}: finished with success',
            ['id' => $message->outboxId]
        );
    }
}
