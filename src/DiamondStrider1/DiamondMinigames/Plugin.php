<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames;

use DiamondStrider1\DiamondMinigames\commands\CommandManager;
use DiamondStrider1\DiamondMinigames\data\MainConfig;
use DiamondStrider1\DiamondMinigames\data\NeoConfig;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use pocketmine\plugin\PluginBase;

class Plugin extends PluginBase
{
  private static Plugin $instance;
  /** @var NeoConfig<MainConfig> */
  private NeoConfig $mainConfig;

  public static function getInstance(): self
  {
    return self::$instance;
  }

  public function onLoad()
  {
    self::$instance = $this;
    $dataFolder = $this->getDataFolder();
    $this->mainConfig = new NeoConfig($dataFolder . "config.yml", MainConfig::class);
  }

  public function onEnable()
  {
    CommandManager::init();
    FormSessions::registerHandlers();
    if ($this->getMainConfig()->logTime) $this->getLogger()->info("THE TIME IS " . date("h:i:s A") . " unix milis");
    $this->reloadPlugin();
  }
  
  public function reloadPlugin(): void
  {
    $this->mainConfig->getObject(true);
    $this->mainConfig->saveData();
  }

  public function setMainConfig(MainConfig $mainConfig): void
  {
    $this->mainConfig->setObject($mainConfig);
  }

  public function getMainConfig(): MainConfig
  {
    return $this->mainConfig->getObject();
  }
}
