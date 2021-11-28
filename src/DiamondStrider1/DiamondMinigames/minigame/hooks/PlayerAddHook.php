<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\hooks;

use DiamondStrider1\DiamondMinigames\minigame\Team;
use pocketmine\Player;

class PlayerAddHook extends BaseHook
{
  public function __construct(
    private Player $player,
    private Team $team,
  ) {
  }

  public function getPlayer(): Player
  {
    return $this->player;
  }

  public function getTeam(): ?Team
  {
    return $this->team;
  }
}
