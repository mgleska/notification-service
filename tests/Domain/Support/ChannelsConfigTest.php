<?php

declare(strict_types=1);

namespace App\Tests\Domain\Support;

use App\Domain\Exception\InvalidConfigException;
use App\Domain\Support\ChannelsConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ChannelsConfigTest extends TestCase
{
    private ChannelsConfig $sut;

    /** @var array<string, string> */
    private array $envCopy;

    protected function setUp(): void
    {
        $this->sut = new ChannelsConfig();
        $this->envCopy = $_ENV;
    }

    protected function tearDown(): void
    {
        $_ENV = $this->envCopy;
    }

    /**
     * @param array<string, string> $channels
     * @param string[] $expected
     */
    #[Test]
    #[DataProvider('dataProviderParseChannels')]
    public function parseChannels(
        ?string $usedChannels,
        array $channels,
        array $expected,
        string $expectedExceptionMessage
    ): void {
        $_ENV = $channels;
        $_ENV['USED_CHANNELS'] = $usedChannels;

        if ($expectedExceptionMessage) {
            $this->expectException(InvalidConfigException::class);
            $this->expectExceptionMessageMatches('/^' . $expectedExceptionMessage . '$/');
        }

        $result = $this->sut->parseChannels();

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function dataProviderParseChannels(): array
    {
        return [
            'USED_CHANNELS-not-set' => [
                'usedChannels' => null,
                'channels' => [],
                'expected' => [],
                'expectedExceptionMessage' => 'Environment variable not found: USED_CHANNELS',
            ],
            'USED_CHANNELS-empty' => [
                'usedChannels' => '',
                'channels' => [],
                'expected' => [],
                'expectedExceptionMessage' => 'Environment variable USED_CHANNELS has empty value.',
            ],
            'not-defined-one-channel' => [
                'usedChannels' => 'CHANNEL_SMS',
                'channels' => [],
                'expected' => [],
                'expectedExceptionMessage' => 'Environment variable not found: CHANNEL_SMS',
            ],
            'not-defined-one-channel-of-two' => [
                'usedChannels' => 'CHANNEL_SMS,CHANNEL_EMAIL',
                'channels' => ['CHANNEL_SMS' => 'xx'],
                'expected' => [],
                'expectedExceptionMessage' => 'Environment variable not found: CHANNEL_EMAIL',
            ],
            'one-channel' => [
                'usedChannels' => 'CHANNEL_SMS',
                'channels' => ['CHANNEL_SMS' => 'xx'],
                'expected' => ['CHANNEL_SMS'],
                'expectedExceptionMessage' => '',
            ],
            'two-channels' => [
                'usedChannels' => 'CHANNEL_SMS,CHANNEL_EMAIL',
                'channels' => ['CHANNEL_SMS' => 'xx', 'CHANNEL_EMAIL' => 'xx'],
                'expected' => ['CHANNEL_SMS', 'CHANNEL_EMAIL'],
                'expectedExceptionMessage' => '',
            ],
            'more-channels-than-used' => [
                'usedChannels' => 'CHANNEL_SMS,CHANNEL_EMAIL',
                'channels' => ['CHANNEL_PUSH' => 'xx', 'CHANNEL_EMAIL' => 'xx', 'CHANNEL_SMS' => 'xx'],
                'expected' => ['CHANNEL_SMS', 'CHANNEL_EMAIL'],
                'expectedExceptionMessage' => '',
            ],
        ];
    }

    /**
     * @param array<string, string> $ENV
     * @param string[] $expected
     */
    #[Test]
    #[DataProvider('dataProviderGetDeliveryServices')]
    public function getDeliveryServices(
        string $channel,
        array $ENV,
        array $expected,
        string $expectedExceptionMessage
    ): void {
        $_ENV = $ENV;

        if ($expectedExceptionMessage) {
            $this->expectException(InvalidConfigException::class);
            $this->expectExceptionMessageMatches('/^' . $expectedExceptionMessage . '$/');
        }

        $result = $this->sut->getDeliveryServices($channel);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function dataProviderGetDeliveryServices(): array
    {
        return [
            'channel-not-found' => [
                'channel' => 'CHANNEL_EMAIL',
                'ENV' => ['CHANNEL_SMS' => 'xx'],
                'expected' => [],
                'expectedExceptionMessage' => 'Environment variable not found: CHANNEL_EMAIL',
            ],
            'channel-found' => [
                'channel' => 'CHANNEL_EMAIL',
                'ENV' => ['CHANNEL_SMS' => 'xx,yy', 'CHANNEL_EMAIL' => 'Service1,Service2'],
                'expected' => ['Service1', 'Service2'],
                'expectedExceptionMessage' => '',
            ],
        ];
    }
}
