<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\events;

use DiamondStrider1\DiamondMinigames\minigame\MGPlayer;

class MGPlayerAdded extends MinigameEvent
{
  public function __construct(
    private MGPlayer $player
  ) {
    parent::__construct($player->getMinigame());
  }

  public function getPlayer(): MGPlayer
  {
    return $this->player;
  }
}
