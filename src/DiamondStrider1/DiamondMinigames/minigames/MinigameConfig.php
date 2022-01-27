<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames;

use DiamondStrider1\DiamondMinigames\minigames\configs\BaseConfig;

class MinigameConfig
{
    /** @var BaseConfig[] */
    public array $configs;

    public function instantiate(): Minigame
    {
        return new Minigame($this->configs);
    }
}
