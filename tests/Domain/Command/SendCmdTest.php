<?php

declare(strict_types=1);

namespace App\Tests\Domain\Command;

use App\Domain\Command\SendCmd;
use App\Domain\Dto\SendDto;
use App\Domain\Repository\InboxRepositoryInterface;
use App\Domain\Repository\OutboxRepositoryInterface;
use App\Domain\Support\ChannelsConfig;
use App\Infrastructure\Repository\InboxRepository;
use App\Infrastructure\Repository\OutboxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RepositoryMock\RepositoryMockObject;
use RepositoryMock\RepositoryMockTrait;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

use function count;

class SendCmdTest extends TestCase
{
    use RepositoryMockTrait;

    private SendCmd $sut;

    private RepositoryMockObject|InboxRepositoryInterface $inboxRepository;
    private RepositoryMockObject|OutboxRepositoryInterface $outboxRepository;
    private MockObject|ChannelsConfig $channelsConfig;
    private MockObject|EntityManagerInterface $entityManager;
    private MessageBusMock $bus;

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function setUp(): void
    {
        $this->inboxRepository = $this->createRepositoryMock(InboxRepository::class);
        $this->outboxRepository = $this->createRepositoryMock(OutboxRepository::class);
        $this->channelsConfig = $this->createMock(ChannelsConfig::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bus = new MessageBusMock();

        $this->sut = new SendCmd(
            $this->inboxRepository,
            $this->outboxRepository,
            $this->channelsConfig,
            $this->entityManager,
            $this->bus
        );
    }

    /**
     * @param string[] $channels
     * @param bool $isExceptionExpected
     *
     * @throws Exception
     * @noinspection PhpUnhandledExceptionInspection
     */
    #[Test]
    #[DataProvider('dataProviderSend')]
    public function send(
        array $channels,
        bool $isExceptionExpected
    ): void {
        $this->channelsConfig->method('parseChannels')->willReturn($channels);

        if (! $isExceptionExpected) {
            $this->entityManager->expects(self::once())->method('commit');
        } else {
            $this->entityManager->method('beginTransaction')->willThrowException(new RuntimeException('test'));
            $this->expectException(RuntimeException::class);
            $this->entityManager->expects(self::once())->method('rollback');
        }

        $dto = new SendDto('user-id', 'email', 'phone', 'token', 'message');
        $result = $this->sut->send($dto);

        $inboxStore = $this->inboxRepository->getStoreContent();
        $outboxStore = $this->outboxRepository->getStoreContent();

        $this->assertCount(1, $inboxStore);
        $this->assertCount(count($channels), $outboxStore);
        $this->assertEquals(count($channels), $this->bus->getCounter());

        $this->assertSame(array_key_last($inboxStore), $result); // send() command returns primary key of just created record
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function dataProviderSend(): array
    {
        return [
            'aaa' => [
                'channels' => ['CHANNEL_SMS'],
                'isExceptionExpected' => false,
            ],
            'baa' => [
                'channels' => ['CHANNEL_SMS'],
                'isExceptionExpected' => true,
            ],
        ];
    }
}

class MessageBusMock implements MessageBusInterface // phpcs:ignore
{
    private int $counter = 0;

    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $this->counter += 1;
        return new Envelope($message);
    }

    public function getCounter(): int
    {
        return $this->counter;
    }
}
