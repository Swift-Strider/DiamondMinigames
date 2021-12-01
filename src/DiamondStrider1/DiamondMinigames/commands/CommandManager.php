<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

use DiamondStrider1\DiamondMinigames\Plugin;

class CommandManager
{
  public static function init(): void
  {
    $plugin = Plugin::getInstance();
    $plugin->getServer()->getCommandMap()->registerAll("diamondminigames", [
      new MainCommand("diamondminigames", "Open DiamondMinigames Management Form", ""),
      new PlayCommand("play", "Join a minigame", "[minigame]", ["game"]),
      new QuitCommand("quit", "Leave a minigame", ""),
    ]);
  }
}
