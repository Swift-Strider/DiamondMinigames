<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\MGPlayer;
use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\minigame\Team;
use pocketmine\player\Player;
use DiamondStrider1\DiamondMinigames\misc\Result;

class PlayerFillFFAImpl extends BasePlayerFillImpl
{
  private Minigame $minigame;

  public function onInit(Minigame $minigame): void
  {
    $this->minigame = $minigame;
  }

  public function onDestroy(): void
  {
  }

  public function addPlayer(MGPlayer $player): array
  {
    $team = new Team("FFA TEAM #" . (count($this->minigame->getTeams()) + 1));
    $this->minigame->setTeams([...$this->minigame->getTeams(), $team]);
    if ($this->minigame->getState() === Minigame::PENDING) $this->minigame->startGame();
    return [Result::ok(), $team];
  }

  public function removePlayer(MGPlayer $player): void
  {
    $this->minigame->setTeams(array_filter(
      $this->minigame->getTeams(),
      function (Team $team) use ($player): bool {
        return !$team->hasPlayer($player);
      }
    ));
  }
}
