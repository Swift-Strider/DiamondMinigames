<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\minigame\impl\PlayerFillFFAImpl;

class PlayerFillFFA extends PlayerFillStrategy
{
  public function createImpl(): PlayerFillFFAImpl
  {
    return new PlayerFillFFAImpl;
  }
}
