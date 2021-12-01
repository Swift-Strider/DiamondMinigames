<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use DiamondStrider1\DiamondMinigames\Plugin;
use pocketmine\Player;

class MinigameManager
{
  /** @var array<string, array<string, Minigame>> MGB->name => [id => Minigame] */
  private array $minigames = [];
  /** @var array<string, Minigame> uuid => Minigame */
  private array $playerMinigames = [];

  public function reset(): void
  {
    foreach ($this->minigames as $minigames) {
      foreach ($minigames as $mg) {
        $mg->endGame(null);
      }
    }
    $this->minigames = [];
    $this->playerMinigames = [];
  }

  /** @return Minigame[] */
  public function getGames(): array
  {
    return array_map(function (array $pair): Minigame {
      return array_values($pair)[0];
    }, array_values($this->minigames));
  }

  public function send(Player $player, string $name): bool
  {
    $mgb = Plugin::getInstance()->getMinigameStore()->get($name);
    if ($mgb === null) {
      return false;
    }
    if (!isset($this->minigames[$mgb->name])) {
      $this->minigames[$mgb->name] = ["xID" . random_int(1000, 9999) => $mg = new Minigame($mgb)];
      if ($mg->addPlayer($player)->success()) {
        $this->playerMinigames[$player->getRawUniqueId()] = $mg;
        return true;
      }
    } else {
      foreach ($this->minigames[$mgb->name] as $mg) {
        if ($mg->addPlayer($player)->success()) {
          $this->playerMinigames[$player->getRawUniqueId()] = $mg;
          return true;
        }
      }
      $this->minigames[$mgb->name] = ["xID" . random_int(1000, 9999) => $mg = new Minigame($mgb)];
      if ($mg->addPlayer($player)->success()) {
        $this->playerMinigames[$player->getRawUniqueId()] = $mg;
        return true;
      }
    }

    return false;
  }

  public function sendSpectator(Player $player, string $id): void
  {
  }

  public function getPlaying(Player $player): ?Minigame
  {
    return $this->playerMinigames[$player->getRawUniqueId()] ?? null;
  }

  public function quitPlayer(Player $player): void
  {
    if (isset($this->playerMinigames[$player->getRawUniqueId()])) {
      $this->playerMinigames[$player->getRawUniqueId()]->removePlayer($player);
      unset($this->playerMinigames[$player->getRawUniqueId()]);
    }
  }
}
