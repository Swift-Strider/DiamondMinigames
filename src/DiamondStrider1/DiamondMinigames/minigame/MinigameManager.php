<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use DiamondStrider1\DiamondMinigames\minigame\events\MGPlayerRemoved;
use DiamondStrider1\DiamondMinigames\minigame\events\MinigameEnd;
use DiamondStrider1\DiamondMinigames\Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

class MinigameManager implements Listener
{
  /** @var array<string, array<string, Minigame>> MGB->name => [id => Minigame] */
  private array $minigames = [];
  /** @var array<string, MGPlayer> uuid => MGPlayer */
  private array $mgPlayers = [];

  public function onMGPlayerRemoved(MGPlayerRemoved $ev): void
  {
    unset($this->mgPlayers[$ev->getPlayer()->getID()]);
  }

  public function onMinigameEnd(MinigameEnd $ev): void
  {
    $mg = $ev->getMinigame();
    $name = $mg->getBlueprint()->name;
    $key = array_search($mg, $this->minigames[$name]);

    foreach ($this->mgPlayers as $id => $player) {
      if ($player->getMinigame() === $mg) {
        unset($this->mgPlayers[$id]);
      }
    }
    if ($key !== false) {
      unset($this->minigames[$name][$key]);
    }
  }

  public function onPlayerQuit(PlayerQuitEvent $ev): void
  {
    if ($mgPlayer = $this->getMGPlayer($ev->getPlayer())) {
      $this->quitPlayer($ev->getPlayer());
    }
  }

  public function reset(): void
  {
    foreach ($this->minigames as $minigames) {
      foreach ($minigames as $mg) {
        $mg->endGame(null);
      }
    }
    $this->minigames = [];
    $this->mgPlayers = [];
  }

  /** @return Minigame[] */
  public function getGames(): array
  {
    $mgs = [];
    foreach ($this->minigames as $games) {
      foreach ($games as $mg) {
        $mgs[] = $mg;
      }
    }
    return $mgs;
  }

  public function send(Player $player, string $name): bool
  {
    $mgb = Plugin::getInstance()->getMinigameStore()->get($name);
    if ($mgb === null) {
      return false;
    }
    if (!isset($this->minigames[$mgb->name])) {
      $this->minigames[$mgb->name] = ["xID" . random_int(1000, 9999) => $mg = new Minigame($mgb)];
      $mgPlayer = new MGPlayer($player, $mg);
      if ($mg->addPlayer($mgPlayer)->success()) {
        $this->mgPlayers[$mgPlayer->getID()] = $mgPlayer;
        return true;
      }
    } else {
      foreach ($this->minigames[$mgb->name] as $mg) {
        $mgPlayer = new MGPlayer($player, $mg);
        if ($mg->addPlayer($mgPlayer)->success()) {
          $this->mgPlayers[$mgPlayer->getID()] = $mgPlayer;
          return true;
        }
      }
      $this->minigames[$mgb->name] = ["xID" . random_int(1000, 9999) => $mg = new Minigame($mgb)];
      $mgPlayer = new MGPlayer($player, $mg);
      if ($mg->addPlayer($mgPlayer)->success()) {
        $this->mgPlayers[$mgPlayer->getID()] = $mgPlayer;
        return true;
      }
    }

    return false;
  }

  public function sendSpectator(Player $player, string $id): void
  {
  }

  public function getMGPlayer(Player $player): ?MGPlayer
  {
    return $this->mgPlayers[$player->getUniqueId()->getBytes()] ?? null;
  }

  public function quitPlayer(Player $player): void
  {
    if (isset($this->mgPlayers[$player->getUniqueId()->getBytes()])) {
      $mgp = $this->mgPlayers[$player->getUniqueId()->getBytes()];
      $mgp->getMinigame()->removePlayer($mgp);
      unset($this->mgPlayers[$player->getUniqueId()->getBytes()]);
    }
  }
}
