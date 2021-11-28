<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\minigame\impl\IStrategyImpl;
use DiamondStrider1\DiamondMinigames\minigame\impl\PlayerFillFFAImpl;

class PlayerFillFFA extends PlayerFillStrategy
{
  public function createImpl(): IStrategyImpl
  {
    return new PlayerFillFFAImpl;
  }
}
