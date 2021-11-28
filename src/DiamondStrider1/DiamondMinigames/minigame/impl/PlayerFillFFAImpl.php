<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerAddHook;
use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\minigame\Team;

class PlayerFillFFAImpl implements IStrategyImpl
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
    if ($this->minigame->getState() === Minigame::PENDING) $this->minigame->startGame();
  }
}
