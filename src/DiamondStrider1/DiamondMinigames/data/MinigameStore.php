<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use AssertionError;
use DiamondStrider1\DiamondMinigames\minigame\MinigameBlueprint;
use DiamondStrider1\DiamondMinigames\Plugin;

class MinigameStore
{
  /** 
   * @phpstan-var array<string, NeoConfig<MinigameBlueprint>>
   * @var NeoConfig[]
   */
  private array $minigameConfigs = [];

  /** @param string $folder path to folder without a trailing slash */
  public function __construct(
    private string $folder,
  ) {
    if (!file_exists($folder)) {
      mkdir($folder, 0777, true);
    }
  }

  public function get(string $name, bool $reload = false): ?MinigameBlueprint
  {
    return $this->getAll($reload)[$name] ?? null;
  }

  /** @return array<string, MinigameBlueprint> */
  public function getAll(bool $reload = false): array
  {
    if ($reload === true) {
      $files = glob($this->folder . "/*.yml");
      if ($files === false) throw new ConfigException("Could not load minigames from folder");
      foreach ($files as $file) {
        $name = substr(basename($file), 0, -4); // removes ".yml" from basename
        $this->minigameConfigs[strtolower($name)] = new NeoConfig($file, MinigameBlueprint::class);
      }
    }
    $minigames = [];
    foreach ($this->minigameConfigs as $name => $config) {
      try {
        $minigames[$name] = $config->getObject($reload);
      } catch (ConfigException $e) {
        Plugin::getInstance()->handleConfigException($e, false);
        unset($this->minigameConfigs[$name]);
      }
    }
    return $minigames;
  }

  public function set(MinigameBlueprint $minigame): void
  {
    $name = strtolower($minigame->name);
    if (!self::checkValidName($name))
      throw new AssertionError("MGBlueprint should insure a valid name");
    if (!isset($this->minigameConfigs[$name])) {
      $file = "$this->folder/$name.yml";
      $this->minigameConfigs[$name] = new NeoConfig($file, MinigameBlueprint::class);
    }
    $this->minigameConfigs[$name]->setObject($minigame);
  }

  public function delete(string $name): void
  {
    if (isset($this->minigameConfigs[$name])) unset($this->minigameConfigs[$name]);
    if (!file_exists($file = "$this->folder/$name.yml")) return;
    unlink($file);
  }

  public static function checkValidName(string $name): bool
  {
    // $name cannot be a number like string("69") because
    // it will be converted to int(69) when used as an array key
    return strlen($name) >= 1 && preg_match("/^[a-zA-Z0-9_]*[a-zA-Z_]$/", $name) === 1;
  }
}
