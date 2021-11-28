<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\Minigame;

class NoWinImpl implements IStrategyImpl
{
  public function onInit(Minigame $minigame): void
  {
  }

  public function onDestroy(): void
  {
  }
}
