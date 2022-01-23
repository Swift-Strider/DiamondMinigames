<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\services;

use DiamondStrider1\DiamondMinigames\Plugin;

class MinigameServices
{
    public static function registerDefaults(): void
    {
        $plugin = Plugin::getInstance();
        $pm = $plugin->getServer()->getPluginManager();
        $pm->registerEvents(new QueueService, $plugin);
    }
}
