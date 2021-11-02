<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use DiamondStrider1\DiamondMinigames\Plugin;
use DiamondStrider1\DiamondMinigames\types\IConfig;
use DiamondStrider1\DiamondMinigames\types\IEditable;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use TypeError;

abstract class ConfigLoader
{
  public static function load(IEditable $config, array $rawData)
  {
    $className = get_class($config);
    try {
      $rClass = new ReflectionClass($className);
    } catch (ReflectionException) {
      throw new TypeError("Class does not exist: $className");
    }
    $constructorParams = $rClass->getConstructor()?->getParameters();
    if ($constructorParams && count($constructorParams) > 0) {
      throw new TypeError("Class's constructor cannot take zero arguments: $className");
    }

    if ($rClass->implementsInterface(IConfig::class)) {
      $defaults = $rClass->getMethod("getDefaults")->invoke(null);
    }

    foreach ($rClass->getProperties(ReflectionProperty::IS_PUBLIC) as $rProp) {
      if ($rProp->isStatic()) continue;

      preg_match_all(
        '/@(\S+) ?(.*)\n?/',
        str_replace("\r\n", "\n", $rProp->getDocComment()),
        $matches
      );
      if (count($matches) < 3) continue;

      $annotations = array_combine($matches[1], $matches[2]);
      $requiredAnnotations = ["type", "config-name", "config-type"];

      if (!$annotations) continue;
      foreach ($requiredAnnotations as $req) {
        if (!isset($annotations[$req])) continue 2;
      }

      $config_name = $annotations["config-name"];
      $config_type = $annotations["config-type"];

      if (
        isset($rawData[$config_name]) &&
        ($config_type !== "object" &&
          gettype($value = $rawData[$config_name]) === $config_type)
      ) {
        $rProp->setValue($config, $value);
      } else if ($config_type !== "object") {
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

  public static function save(IEditable $config): array
  {
    $className = get_class($config);
    $rawData = [];
    try {
      $rClass = new ReflectionClass($className);
    } catch (ReflectionException) {
      throw new TypeError("Class does not exist: $className");
    }
    $constructorParams = $rClass->getConstructor()?->getParameters();
    if ($constructorParams && count($constructorParams) > 0) {
      throw new TypeError("Class's constructor cannot take zero arguments: $className");
    }

    foreach ($rClass->getProperties(ReflectionProperty::IS_PUBLIC) as $rProp) {
      if ($rProp->isStatic()) continue;

      preg_match_all(
        '/@(\S+) ?(.*)\n?/',
        str_replace("\r\n", "\n", $rProp->getDocComment()),
        $matches
      );
      if (count($matches) < 3) continue;

      $annotations = array_combine($matches[1], $matches[2]);
      $requiredAnnotations = ["type", "config-name", "config-type"];

      if (!$annotations) continue;
      foreach ($requiredAnnotations as $req) {
        if (!isset($annotations[$req])) continue 2;
      }

      $config_name = $annotations["config-name"];
      $rawData[$config_name] = $rProp->getValue($config);
    }
    return $rawData;
  }
}
