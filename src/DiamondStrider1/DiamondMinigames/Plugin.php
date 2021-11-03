<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames;

use DiamondStrider1\DiamondMinigames\commands\CommandManager;
use DiamondStrider1\DiamondMinigames\data\ConfigLoader;
use DiamondStrider1\DiamondMinigames\data\MainConfig;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use pocketmine\plugin\PluginBase;

class Plugin extends PluginBase
{
  private static Plugin $instance;
  private MainConfig $config;

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
    FormSessions::registerHandlers();
    $this->config = new MainConfig;
    $this->reloadPlugin();
  }
  
  public function reloadPlugin(): void
  {
    $this->reloadConfig();
    ConfigLoader::load($this->config, $this->getConfig()->getAll());
  }

  public function saveMainConfig(MainConfig $mainConfig = null): void
  {
    $this->config = $mainConfig ?? $this->config;
    
    $this->getConfig()->setAll(ConfigLoader::unload($this->config));
    $this->saveConfig();
  }

  public function getMainConfig(): MainConfig
  {
    return $this->config;
  }
}
