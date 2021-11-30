<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\hooks;

use DiamondStrider1\DiamondMinigames\minigame\Team;

class MinigameEndHook extends BaseHook
{
  public function __construct(
    private ?Team $winner
  ) {
  }

  public function getWinningTeam(): ?Team
  {
    return $this->winner;
  }
}
