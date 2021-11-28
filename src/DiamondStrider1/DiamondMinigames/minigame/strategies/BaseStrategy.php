<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\minigame\impl\IStrategyImpl;

abstract class BaseStrategy
{
  abstract public function createImpl(): IStrategyImpl;
}
