<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\events;

use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use pocketmine\event\Event;

abstract class MinigameEvent extends Event
{
  public function __construct(
    private Minigame $minigame
  ) {
  }

  public function getMinigame(): Minigame
  {
    return $this->minigame;
  }
}
