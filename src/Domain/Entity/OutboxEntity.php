<?php

namespace App\Domain\Entity;

use App\Domain\Enum\DeliveryStatusEnum;
use App\Infrastructure\Repository\OutboxRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "outbox")]
#[ORM\Entity(repositoryClass: OutboxRepository::class)]
class OutboxEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $inboxId;

    #[ORM\Column(length: 100)]
    private string $channel;

    #[ORM\Column(enumType: DeliveryStatusEnum::class)]
    private DeliveryStatusEnum $deliveryStatus;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $tryNumber;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deliveredAt = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getInboxId(): int
    {
        return $this->inboxId;
    }

    public function setInboxId(int $inboxId): static
    {
        $this->inboxId = $inboxId;

        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function getDeliveryStatus(): DeliveryStatusEnum
    {
        return $this->deliveryStatus;
    }

    public function setDeliveryStatus(DeliveryStatusEnum $deliveryStatus): static
    {
        $this->deliveryStatus = $deliveryStatus;

        return $this;
    }

    public function getTryNumber(): int
    {
        return $this->tryNumber;
    }

    public function setTryNumber(int $tryNumber): static
    {
        $this->tryNumber = $tryNumber;

        return $this;
    }

    public function getDeliveredAt(): ?DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(DateTimeImmutable $deliveredAt): static
    {
        $this->deliveredAt = $deliveredAt;

        return $this;
    }
}
