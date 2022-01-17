<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames;

use DiamondStrider1\DiamondMinigames\commands\CommandManager;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use DiamondStrider1\DiamondMinigames\misc\MainConfig;
use DiamondStrider1\DiamondMinigames\data\NeoConfig;
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

  protected function onLoad(): void
  {
    self::$instance = $this;
    $dataFolder = $this->getDataFolder();
    $this->mainConfig = new NeoConfig($dataFolder . "config.yml", MainConfig::class);
  }

  protected function onEnable(): void
  {
    CommandManager::init();
    $this->reloadPlugin();
  }

  protected function onDisable(): void
  {
  }

  public function reloadPlugin(): void
  {
    try {
      $this->mainConfig->getObject(true);
    } catch (ConfigException $e) {
      $this->handleConfigException($e, true);
      return;
    }
  }

  public function handleConfigException(ConfigException $e, bool $fatal): void
  {
    $this->getLogger()->emergency("Error while Loading!\n\n§l§c{$e->getMessage()}\n");
    foreach (explode("\n", $e->getTraceAsString()) as $line)
      $this->getLogger()->debug("Stack Trace: §c" . $line);
    if ($fatal)
      $this->getServer()->getPluginManager()->disablePlugin($this);
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
