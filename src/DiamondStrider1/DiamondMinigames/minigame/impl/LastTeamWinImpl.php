<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;

class LastTeamWinImpl implements IStrategyImpl, Listener
{
  private Minigame $minigame;
  public function onInit(Minigame $minigame): void
  {
    $this->minigame = $minigame;
    $plugin = Plugin::getInstance();
    $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
  }

  public function onDestroy(): void
  {
  }

  public function onPlayerDeath(PlayerDeathEvent $ev): void
  {
    if ($this->minigame->getState() !== Minigame::RUNNING) return;
    $died = $ev->getPlayer();
    if (!$this->minigame->hasPlayer($died)) return;

    $this->minigame->removePlayer($died);
    if (count($aliveTeams = $this->minigame->getPlayingTeams()) === 1) {
      $this->minigame->endGame($aliveTeams[0]);
    }
  }
}
