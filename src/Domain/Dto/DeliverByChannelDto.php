<?php

declare(strict_types=1);

namespace App\Domain\Dto;

class DeliverByChannelDto
{
    public readonly string $channel;
    public readonly int $outboxId;
    public readonly string $message;
    public readonly ?string $email;
    public readonly ?string $phoneNumber;
    public readonly ?string $pushToken;

    public function __construct(
        string $channel,
        int $outboxId,
        string $message,
        ?string $email,
        ?string $phoneNumber,
        ?string $pushToken
    ) {
        $this->channel = $channel;
        $this->outboxId = $outboxId;
        $this->message = $message;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->pushToken = $pushToken;
    }
}
