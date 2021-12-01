<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use pocketmine\Player;

class MGPlayer
{
  private bool $eliminated = false;

  public function __construct(
    private Player $player,
    private Minigame $minigame
  ) {
  }

  public function getPlayer(): Player
  {
    return $this->player;
  }

  public function getID(): string
  {
    return $this->player->getRawUniqueId();
  }

  public function getMinigame(): Minigame
  {
    return $this->minigame;
  }

  public function setEliminated(bool $eliminated): void
  {
    $this->eliminated = $eliminated;
  }

  public function isEliminated(): bool
  {
    return $this->eliminated;
  }
}
