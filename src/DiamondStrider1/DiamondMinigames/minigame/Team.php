<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use pocketmine\Player;

class Team
{
  /** @var array<string, Player> uuid => Player */
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

  public function addPlayer(Player $player): void
  {
    $this->players[$player->getRawUniqueId()] = $player;
  }

  public function removePlayer(Player $player): void
  {
    unset($this->players[$player->getRawUniqueId()]);
  }

  public function hasPlayer(Player $player): bool
  {
    return isset($this->players[$player->getRawUniqueId()]);
  }

  /** @return array<string, Player> uuid => Player */
  public function getPlayers(): array
  {
    return $this->players;
  }
}
