<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use DiamondStrider1\DiamondMinigames\data\metadata\ObjectType;
use DiamondStrider1\DiamondMinigames\Plugin;

/**
 * @template T of object
 */
class NeoConfig
{
  /** @var T|null */
  private ?object $lastObject = null;

  /** @phpstan-param class-string<T> $class */
  public function __construct(
    private string $filename,
    private string $class,
  ) {
    if (is_dir($filename)) {
      $context = new ConfigContext($filename);
      throw new ConfigException("YAML file is a directory: §c\"§7{$filename}§c\"", $context);
    }
    if (!file_exists($filename)) {
      file_put_contents($filename, yaml_emit([])); // Initialize YAML file
      Plugin::getInstance()->getLogger()->debug("Made YAML file at \"$filename\"");
    }
  }

  /**
   * @phpstan-return T
   */
  public function getObject(bool $reload = false): object
  {
    if ($this->lastObject && !$reload) {
      return $this->lastObject;
    }

    $context = new ConfigContext($this->filename);
    $object = (new ObjectType($this->class))->fromRaw($this->fetchData(), $context);
    $this->setObject($object);
    return $object;
  }

  /**
   * @phpstan-param T $object
   */
  public function setObject(object $object): void
  {
    $this->lastObject = $object;
    $context = new ConfigContext($this->filename);
    $contents = (new ObjectType($this->class))->yamlLines($object, $context);
    file_put_contents($this->filename, $contents);
  }

  /** @return array<string, mixed> */
  private function fetchData(): array
  {
    $context = new ConfigContext($this->filename);
    $contents = file_get_contents($this->filename);
    if ($contents === false) {
      throw new ConfigException("Could not load settings", $context);
    }

    $saveData = yaml_parse($contents) ?? []; // Empty file defaults to empty array
    if (!is_array($saveData)) {
      throw new ConfigException("Settings must be in key-value pairs", $context);
    }

    return $saveData;
  }
}
