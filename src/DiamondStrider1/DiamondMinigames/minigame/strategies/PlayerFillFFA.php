<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerAddHook;
use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\minigame\Team;
use DiamondStrider1\DiamondMinigames\types\IValid;

class PlayerFillFFA extends PlayerFillStrategy
{
  public function createImpl(): IStrategyImpl
  {
    return new class implements IStrategyImpl
    {
      private Minigame $minigame;
      public function onInit(Minigame $minigame): void
      {
        $this->minigame = $minigame;
      }

      public function onDestroy(): void
      {
      }

      public function onPlayerAdd(PlayerAddHook $hook): void
      {
        $team = new Team("FFA TEAM #" . (count($this->minigame->getTeams()) + 1));
        $hook->setTeam($team);
      }
    };
  }
}
