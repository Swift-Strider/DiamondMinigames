<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames;

use DiamondStrider1\DiamondMinigames\minigames\configs\BaseConfig;
use DiamondStrider1\DiamondMinigames\minigames\configs\ConfigurableTrait;

class Minigame
{
    use ConfigurableTrait;

    /**
     * @param array<BaseConfig> $configs
     */
    public function __construct(array $configs)
    {
        $this->useConfigs($configs);
    }
}
