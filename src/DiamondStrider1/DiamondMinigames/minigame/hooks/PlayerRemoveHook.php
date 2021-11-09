<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\hooks;

use pocketmine\Player;

class PlayerRemoveHook extends BaseHook
{
  public function __construct(
    private Player $player,
  ) {
  }

  public function getPlayer(): Player
  {
    return $this->player;
  }
}
