<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

use CortexPE\Commando\PacketHooker;
use DiamondStrider1\DiamondMinigames\Plugin;

class CommandManager
{
  public static function init()
  {
    $plugin = Plugin::getInstance();

    if (!PacketHooker::isRegistered()) {
      PacketHooker::register($plugin);
    }

    $plugin->getServer()->getCommandMap()->registerAll("diamondminigames", [
      new MainCommand($plugin, "diamondminigames", "Open DiamondMinigames Management Form"),
    ]);
  }
}
