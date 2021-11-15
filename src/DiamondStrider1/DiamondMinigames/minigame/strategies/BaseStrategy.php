<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

abstract class BaseStrategy
{
  abstract public function createImpl(): IStrategyImpl;
}
