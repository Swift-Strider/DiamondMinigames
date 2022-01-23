<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames;

use DiamondStrider1\DiamondMinigames\minigames\configs\ConfigManager;

class Minigame
{
    private ConfigManager $configManager;

    /**
     * @param array<object> $configs
     */
    public function __construct(array $configs)
    {
        $this->configManager = new ConfigManager($configs);
    }

    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }
}
