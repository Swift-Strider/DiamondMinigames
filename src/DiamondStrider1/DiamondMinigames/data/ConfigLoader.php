<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use DiamondStrider1\DiamondMinigames\Plugin;
use DiamondStrider1\DiamondMinigames\types\IConfig;
use DiamondStrider1\DiamondMinigames\types\IEditable;
use ReflectionClass;
use ReflectionProperty;
use TypeError;

abstract class ConfigLoader
{
  /** @param mixed[] $rawData */
  public static function load(IEditable $config, array $rawData): void
  {
    $className = get_class($config);
    $rClass = new ReflectionClass($className);

    $constructorParams = $rClass->getConstructor()?->getParameters();
    if ($constructorParams) {  // An empty array is falsy in php
      throw new TypeError("Class's constructor cannot take zero arguments: $className");
    }

    if ($config instanceof IConfig) {
      $defaults = $config::getDefaults();
    }

    foreach (self::getConfigInfo($config) as $info) {
      $config_name = $info["annotations"]["config-name"];
      $config_type = $info["annotations"]["config-type"];
      $rProp = $info["prop_ref"];

      if ($config_type === "object") {
        continue;
      }

      if (
        isset($rawData[$config_name]) &&
        gettype($value = $rawData[$config_name]) === $config_type
      ) {
        $rProp->setValue($config, $value);
      } else {
        [$expected, $got] = [$config_type, gettype($rawData[$config_name] ?? null)];
        Plugin::getInstance()->getLogger()->debug(
          get_class($config) . ": " . $config_name .
            " rejected because of type mis-match. Expected $expected but got $got."
        );
        if (isset($defaults)) {
          $rProp->setValue($config, $defaults[$config_name]);
        }
      }
    }
  }

  /** @return array<string, mixed> */
  public static function unload(IEditable $config): array
  {
    $className = get_class($config);
    /** @var array<string, mixed> */
    $rawData = [];
    $rClass = new ReflectionClass($className);

    $constructorParams = $rClass->getConstructor()?->getParameters();
    if ($constructorParams) { // PHP still considers empty arrays falsy
      throw new TypeError("Class's constructor cannot take zero arguments: $className");
    }

    foreach (self::getConfigInfo($config) as $info) {
      $config_name = $info["annotations"]["config-name"];
      $rawData[$config_name] = $info["prop_ref"]->getValue($config);
    }
    return $rawData;
  }

  /**
   * @return array<string, array{
   *     prop_ref: ReflectionProperty,
   *     annotations: (array{type: string,
   *        config-name: string, config-type: string
   *        } & array<string, string>)
   *    }>
   */
  private static function getConfigInfo(IEditable $config): array
  {
    $className = get_class($config);
    $rClass = new ReflectionClass($className);

    $constructorParams = $rClass->getConstructor()?->getParameters();
    if ($constructorParams) { // PHP still considers empty arrays falsy
      throw new TypeError("Class's constructor cannot take zero arguments: $className");
    }

    $props = [];
    foreach ($rClass->getProperties(ReflectionProperty::IS_PUBLIC) as $rProp) {
      if ($rProp->isStatic()) continue;
      $docComment = $rProp->getDocComment();
      if (!$docComment) $docComment = "";

      preg_match_all(
        '/@(\S+) ?(.*)\n?/',
        str_replace("\r\n", "\n", $docComment),
        $matches
      );
      if (count($matches) < 3) continue;

      $annotations = array_combine($matches[1], $matches[2]);
      $requiredAnnotations = ["type", "config-name", "config-type"];

      if (!$annotations) continue;
      $props[$rProp->getName()] = [];
      foreach ($requiredAnnotations as $req) {
        if (!isset($annotations[$req])) {
          unset($props[$rProp->getName()]);
          continue 2;
        }
      }
      $props[$rProp->getName()] = $annotations[$req];
    }
    return $props;
  }
}
