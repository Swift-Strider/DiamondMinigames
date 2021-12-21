<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use pocketmine\player\Player;

class Team
{
  /** @var array<string, MGPlayer> uuid => MGPlayer */
  private $players = [];

  public function __construct(
    private string $name,
  ) {
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function addPlayer(MGPlayer $player): void
  {
    $this->players[$player->getID()] = $player;
  }

  public function removePlayer(MGPlayer $player): void
  {
    unset($this->players[$player->getID()]);
  }

  public function hasPlayer(MGPlayer $player): bool
  {
    return isset($this->players[$player->getID()]);
  }

  public function isEliminated(): bool
  {
    foreach ($this->players as $player)
      if (!$player->isEliminated()) return false;
    return true;
  }

  /** @return array<string, MGPlayer> uuid => MGPlayer */
  public function getPlayers(): array
  {
    return $this->players;
  }
}
