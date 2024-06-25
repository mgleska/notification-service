<?php

declare(strict_types=1);

namespace App\Domain\Dto;

class DeliverByServiceDto
{
    public readonly string $serviceName;
    public readonly string $message;
    public readonly ?string $email;
    public readonly ?string $phoneNumber;
    public readonly ?string $pushToken;

    public function __construct(
        string $serviceName,
        string $message,
        ?string $email,
        ?string $phoneNumber,
        ?string $pushToken,
    ) {
        $this->serviceName = $serviceName;
        $this->message = $message;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->pushToken = $pushToken;
    }
}
