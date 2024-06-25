<?php

declare(strict_types=1);

namespace App\Domain\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class SendDto
{
    #[Assert\Length(exactly: 36)]
    #[Assert\Uuid]
    #[OA\Property(example: '5689c3cf-5acf-4c14-bc32-8e0e6927a061')]
    public readonly string $userId;

    #[Assert\Length(min: 3, max: 255)]
    #[OA\Property(example: 'john@email.com')]
    public readonly ?string $email;

    #[Assert\Length(min: 3, max: 50)]
    #[OA\Property(example: '+48-123-456-789')]
    public readonly ?string $phoneNumber;

    #[Assert\Length(min: 3, max: 255)]
    #[OA\Property(example: '123abcd')]
    public readonly ?string $pushToken;

    #[Assert\Length(min: 1, max: 1000)]
    #[OA\Property(example: 'Order number 1234 is ready for collection from the parcel locker.')]
    public readonly string $message;

    public function __construct(
        string $userId,
        ?string $email,
        ?string $phoneNumber,
        ?string $pushToken,
        string $message
    ) {
        $this->userId = $userId;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->pushToken = $pushToken;
        $this->message = $message;
    }
}
