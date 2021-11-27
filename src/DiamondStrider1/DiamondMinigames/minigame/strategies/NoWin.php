<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\minigame\Minigame;

class NoWin extends WinStrategy
{
  public function createImpl(): IStrategyImpl
  {
    return new class implements IStrategyImpl
    {
      public function onInit(Minigame $minigame): void
      {
      }

      public function onDestroy(): void
      {
      }
    };
  }
}
