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
    return $this->getAll($reload)[strtolower($name)] ?? null;
  }

  /** @return array<string, MinigameBlueprint> */
  public function getAll(bool $reload = false): array
  {
    if ($reload === true) {
      $this->minigameConfigs = []; // Reset 
      $files = glob($this->folder . "/*.yml");
      if ($files === false) throw new ConfigException("Could not load minigames from folder");
      foreach ($files as $file) {
        $conf = new NeoConfig($file, MinigameBlueprint::class);
        try {
          $name = $conf->getObject(true)->name;
        } catch (ConfigException $e) {
          Plugin::getInstance()->handleConfigException($e, false);
          continue;
        }
        $this->minigameConfigs[strtolower($name)] = $conf;
      }
    }
    $minigames = [];
    foreach ($this->minigameConfigs as $name => $config) {
      // ConfigExceptions will never happen because of code above
      $minigames[$name] = $config->getObject();
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
    $name = strtolower($name);
    if (isset($this->minigameConfigs[$name])) {
      $this->minigameConfigs[$name]->deleteFile();
      unset($this->minigameConfigs[$name]);
    }
  }

  public static function checkValidName(string $name): bool
  {
    // $name cannot be a number like string("69") because
    // it will be converted to int(69) when used as an array key
    return strlen($name) >= 1 && preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/", $name) === 1;
  }
}
