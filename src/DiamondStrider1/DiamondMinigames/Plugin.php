<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames;

use DiamondStrider1\DiamondMinigames\commands\CommandManager;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use DiamondStrider1\DiamondMinigames\data\MainConfig;
use DiamondStrider1\DiamondMinigames\data\MinigameStore;
use DiamondStrider1\DiamondMinigames\data\NeoConfig;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use pocketmine\plugin\PluginBase;

class Plugin extends PluginBase
{
  private static Plugin $instance;
  /** @var NeoConfig<MainConfig> */
  private NeoConfig $mainConfig;
  /** @var MinigameStore */
  private MinigameStore $mgStore;

  public static function getInstance(): self
  {
    return self::$instance;
  }

  public function onLoad()
  {
    self::$instance = $this;
    $dataFolder = $this->getDataFolder();
    $this->mainConfig = new NeoConfig($dataFolder . "config.yml", MainConfig::class);
    $this->mgStore = new MinigameStore($dataFolder . "minigames");
  }

  public function onEnable()
  {
    CommandManager::init();
    FormSessions::registerHandlers();
    $this->reloadPlugin();
  }

  public function reloadPlugin(): void
  {
    try {
      $this->mainConfig->getObject(true);
    } catch (ConfigException $e) {
      $this->getLogger()->emergency("Could Not Load Config: §3" . $e->getMessage());
      $this->getServer()->getPluginManager()->disablePlugin($this);
    }
    try {
      $this->mgStore->getMinigames(true);
    } catch (ConfigException $e) {
      $this->getLogger()->emergency("Could Not Load Minigames Folder: §3" . $e->getMessage());
      $this->getServer()->getPluginManager()->disablePlugin($this);
    }
  }

  public function setMainConfig(MainConfig $mainConfig): void
  {
    $this->mainConfig->setObject($mainConfig);
  }

  public function getMainConfig(): MainConfig
  {
    return $this->mainConfig->getObject();
  }

  public function getMinigameStore(): MinigameStore
  {
    return $this->mgStore;
  }
}
