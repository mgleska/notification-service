<?php

declare(strict_types=1);

namespace App\Tests\Domain\Command;

use App\Domain\Command\DeliverByChannelCmd;
use App\Domain\Dto\DeliverByChannelDto;
use App\Domain\Repository\OutboxRepositoryInterface;
use App\Domain\Support\ChannelsConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

use function count;

class DeliverByChannelCmdTest extends TestCase
{
    private DeliverByChannelCmd $sut;

    private MockObject|ChannelsConfig $channelsConfig;
    private MockObject|OutboxRepositoryInterface $outboxRepository;
    private MockObject|MessageBusInterface $bus;

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function setUp(): void
    {
        $this->channelsConfig = $this->createMock(ChannelsConfig::class);
        $this->outboxRepository = $this->createMock(OutboxRepositoryInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->sut = new DeliverByChannelCmd(
            $this->channelsConfig,
            $this->outboxRepository,
            $this->bus,
            $logger
        );
    }

    /**
     * @param string[] $services
     * @param array<int, array<int, bool|null>> $serviceResponses
     * @param bool $expected
     */
    #[Test]
    #[DataProvider('dataProviderDeliverByChannel')]
    public function deliverByChannel(
        array $services,
        array $serviceResponses,
        bool $expected
    ): void {
        $this->channelsConfig->method('getDeliveryServices')->willReturn($services);
        $this->outboxRepository->expects(self::once())->method('advanceTryNumber');

        $this->bus->expects(self::exactly(count($services)))->method('dispatch')
            ->willReturnOnConsecutiveCalls(...$this->createEnvelopes($serviceResponses));

        $dto = new DeliverByChannelDto('channel', 1, 'message', 'email', 'phone', 'token');
        $result = $this->sut->deliverByChannel($dto);

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function dataProviderDeliverByChannel(): array
    {
        return [
            'no-delivery-class-defined' => [
                'services' => ['SMS1'],
                'serviceResponses' => [1 => []],
                'expected' => false,
            ],
            'no-delivery-class-identifies-itself-as-SMS1' => [
                'services' => ['SMS1'],
                'serviceResponses' => [1 => [null, null, null]],
                'expected' => false,
            ],
            'SMS1-delivery-class-exists-but-delivery-failed' => [
                'services' => ['SMS1'],
                'serviceResponses' => [1 => [null, false, null]],
                'expected' => false,
            ],
            'SMS1-delivery-success' => [
                'services' => ['SMS1'],
                'serviceResponses' => [1 => [null, true, null]],
                'expected' => true,
            ],
            'delivery-by-backup-service' => [
                'services' => ['SMS1', 'SmsBackup'],
                'serviceResponses' => [1 => [null, false, null], 2 => [null, null, true]],
                'expected' => true,
            ],
        ];
    }

    /**
     * @param array<int, array<int, bool|null>> $serviceResponses
     * @return Envelope[]
     */
    private function createEnvelopes(array $serviceResponses): array
    {
        $result = [];
        foreach ($serviceResponses as $iteration => $responses) {
            $stamps = [];
            foreach ($responses as $response) {
                $stamps[] = new HandledStamp($response, 'className');
            }
            $envelope = new Envelope(new \stdClass(), $stamps);
            $result[$iteration] = $envelope;
        }

        return $result;
    }
}
