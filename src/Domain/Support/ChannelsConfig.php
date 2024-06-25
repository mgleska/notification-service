<?php

declare(strict_types=1);

namespace App\Domain\Support;

use App\Domain\Exception\InvalidConfigException;

use function explode;

class ChannelsConfig
{
    /**
     * @return string[]
     */
    public function parseChannels(): array
    {
        if (! isset($_ENV['USED_CHANNELS'])) {
            throw new InvalidConfigException('Environment variable not found: USED_CHANNELS');
        }

        if (trim($_ENV['USED_CHANNELS']) === '') {
            throw new InvalidConfigException('Environment variable USED_CHANNELS has empty value.');
        }

        $channels = [];
        foreach (explode(',', $_ENV['USED_CHANNELS']) as $channel) {
            if (! isset($_ENV[$channel])) {
                throw new InvalidConfigException('Environment variable not found: ' . $channel);
            }
            $channels[] = $channel;
        }

        return $channels;
    }

    /**
     * @return string[]
     */
    public function getDeliveryServices(string $channel): array
    {
        if (! isset($_ENV[$channel])) {
            throw new InvalidConfigException('Environment variable not found: ' . $channel);
        }

        return explode(',', $_ENV[$channel]);
    }
}
