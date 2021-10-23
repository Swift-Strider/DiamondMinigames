<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames;

use DiamondStrider1\DiamondMinigames\commands\CommandManager;
use pocketmine\plugin\PluginBase;

class Plugin extends PluginBase
{
  private static Plugin $instance;

  public static function getInstance(): self
  {
    return self::$instance;
  }

  public function onLoad()
  {
    self::$instance = $this;
  }

  public function onEnable()
  {
    CommandManager::init();
  }
}
