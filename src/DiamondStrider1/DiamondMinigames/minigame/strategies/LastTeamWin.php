<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;

class LastTeamWin extends WinStrategy
{
  public function createImpl(): IStrategyImpl
  {
    return new class implements IStrategyImpl
    {
      public function onInit(Minigame $minigame): void
      {
        $plugin = Plugin::getInstance();
        $plugin->getServer()->getPluginManager()->registerEvents(new class($minigame) implements Listener
        {
          public function __construct(
            private Minigame $minigame
          ) {
          }

          public function onPlayerDeath(PlayerDeathEvent $ev): void {
            $died = $ev->getPlayer();
            if (!$this->minigame->hasPlayer($died)) return;
            
            $this->minigame->removePlayer($died);
            if (count($aliveTeams = $this->minigame->getPlayingTeams()) === 1) {
              $this->minigame->endGame($aliveTeams[0]);
            }
          }
        }, $plugin);
      }

      public function onDestroy(): void
      {
      }
    };
  }
}
