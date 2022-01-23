<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\services;

use DiamondStrider1\DiamondMinigames\minigames\configs\QueueConfig;
use DiamondStrider1\DiamondMinigames\Plugin;

class MinigameServices
{
    /** @var ServiceInfo[] */
    private static array $serviceInfos;

    public static function registerDefaults(): void
    {
        $plugin = Plugin::getInstance();
        $pm = $plugin->getServer()->getPluginManager();

        self::register(new ServiceInfo(QueueService::class, QueueConfig::class));
        $pm->registerEvents(new QueueService, $plugin);
    }

    public static function register(ServiceInfo $serviceInfo): void
    {
        self::$serviceInfos[] = $serviceInfo;
    }

    /**
     * @return ServiceInfo[]
     */
    public static function getAll(): array
    {
        return self::$serviceInfos;
    }
}
