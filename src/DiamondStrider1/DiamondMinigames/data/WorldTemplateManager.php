<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use pocketmine\Server;

class WorldTemplateManager
{
  /** @var array<string, WorldTemplate> */
  private array $templates = [];

  /** @param string $folder path to folder without a trailing slash */
  public function __construct(
    private string $folder,
  ) {
    if (!file_exists($folder)) {
      mkdir($folder, 0777, true);
    }
  }

  public function getFolder(): string
  {
    return $this->folder;
  }

  /** @return array<string, WorldTemplate> */
  public function getAll(bool $reload = false): array
  {
    if ($reload) {
      $this->templates = [];
      $scan = scandir($this->folder);
      if ($scan === false) throw new ConfigException("Could not scan for World Templates", new ConfigContext($this->folder));
      foreach ($scan as $candidate) {
        if ($candidate == "." || $candidate == "..") continue;
        if (!is_dir("$this->folder/$candidate")) continue;
        $this->templates[$candidate] = new WorldTemplate($candidate, $this);
      }
    }
    return $this->templates;
  }

  public function get(string $name): ?WorldTemplate
  {
    return $this->templates[$name] ?? null;
  }

  public function add(string $name, string $worldName): WorldTemplate
  {
    $this->templates[$name] = new WorldTemplate($name, $this);
    $f = Server::getInstance()->getDataPath() . "worlds/$worldName";
    $wm = Server::getInstance()->getWorldManager();
    if (($w = $wm->getWorldByName($worldName)) !== null) $wm->unloadWorld($w);
    self::recursiveCopy($f, $this->folder . "/$name");
    return $this->templates[$name];
  }

  public function delete(string $name): void
  {
    if (isset($this->templates[$name])) unset($this->templates[$name]);
    self::recursiveUnlink("$this->folder/$name");
  }

  public static function recursiveCopy(string $src, string $dst): void
  {
    if (!file_exists($dst)) mkdir($dst, 0777, true);
    $found = scandir($src);
    if ($found === false) throw new ConfigException("Could not read folder \"$src\" while copying to folder \"$dst\"", new ConfigContext($dst));
    foreach ($found as $f) {
      if ($f === "." || $f === "..") continue;
      if (is_file("$src/$f")) {
        copy("$src/$f", "$dst/$f");
      }
      if (is_dir("$src/$f")) {
        self::recursiveCopy("$src/$f", "$dst/$f");
      }
    }
  }

  public static function recursiveUnlink(string $dir): void
  {
    $found = scandir($dir);
    if ($found === false) throw new ConfigException("Could not remove folder", new ConfigContext($dir));
    foreach ($found as $f) {
      if ($f === "." || $f === "..") continue;
      $path = "$dir/$f";
      if (is_file($path)) {
        unlink($path);
      }
      if (is_dir($path)) {
        self::recursiveUnlink($path);
      }
    }
    rmdir($dir);
  }
}
