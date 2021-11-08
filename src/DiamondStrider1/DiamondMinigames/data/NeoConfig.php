<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use DiamondStrider1\DiamondMinigames\Plugin;
use DiamondStrider1\DiamondMinigames\types\IConfig;
use DiamondStrider1\DiamondMinigames\types\IEditable;
use DiamondStrider1\DiamondMinigames\types\ISubtyped;
use DiamondStrider1\DiamondMinigames\types\IValid;
use pocketmine\math\Vector3;
use ReflectionClass;
use ReflectionProperty;
use TypeError;

/**
 * @template T of IEditable
 */
class NeoConfig
{
  /** @var mixed[] */
  private array $data = [];
  /** @var T|null */
  private ?IEditable $lastObject = null;

  /** @phpstan-param class-string<T> $class */
  public function __construct(
    private string $filename,
    private string $class,
    private ?string $base_offset = null,
  ) {
    if (is_dir($filename)) {
      throw ConfigException::fileIsDirectory("YAML file is a directory: §c\"§7{$filename}§c\"");
    }
    if (!file_exists($filename)) {
      file_put_contents($filename, yaml_emit([])); // Initialize YAML file
      Plugin::getInstance()->getLogger()->debug("Made YAML file at \"$filename\"");
    }
  }

  /**
   * @phpstan-return T
   */
  public function getObject(bool $reload = false): IEditable
  {
    if ($this->lastObject && !$reload) {
      return $this->lastObject;
    }

    $rClass = new ReflectionClass($this->class);
    $constructorParams = $rClass->getConstructor()?->getParameters();
    if ($constructorParams) {  // An empty array is falsy in php
      throw new TypeError("Class's constructor cannot take zero arguments: {$this->class}");
    }

    $object = new $this->class;
    self::load($object, $this->fetchData());
    $this->setObject($object);

    return $object;
  }

  /**
   * @phpstan-param T $object
   */
  public function setObject(IEditable $object): void
  {
    $this->lastObject = $object;
    $this->data = self::unload($object);
    $this->saveData();
  }

  public function saveData(): void
  {
    if ($this->base_offset === null) {
      $contents = $this->injectComments(yaml_emit($this->data));
      file_put_contents($this->filename, $contents);
      return;
    }

    $contents = file_get_contents($this->filename);
    if ($contents === false) {
      throw ConfigException::unknownError("Could not load settings");
    }

    $saveData = yaml_parse($contents) ?? []; // Empty file default to empty array
    if (!is_array($saveData)) {
      throw ConfigException::unknownError("Settings must be in key-value pairs");
    }

    $oldData = &$saveData;
    $offsets = explode(".", $this->base_offset);
    foreach ($offsets as $i) {
      if (!isset($oldData[$i])) $oldData[$i] = [];
      $oldData = &$oldData[$i];
    }
    $oldData = &$this->data;

    file_put_contents($this->filename, yaml_emit($saveData)); // comments won't be injected
  }

  /** @return array<string, mixed> */
  private function fetchData(): array
  {
    $contents = file_get_contents($this->filename);
    if ($contents === false) {
      throw ConfigException::typeMismatch("Could not load settings");
    }

    $saveData = yaml_parse($contents) ?? []; // Empty file default to empty array
    if (!is_array($saveData)) {
      throw ConfigException::unknownError("Settings must be in key-value pairs");
    }

    if ($this->base_offset !== null) {
      $offsets = explode(".", $this->base_offset);
      foreach ($offsets as $i) {
        if (!isset($oldData[$i])) $oldData[$i] = [];
        $saveData = &$oldData[$i];
      }
    }

    return $saveData;
  }

  /**
   * LIMITATION: Only works with top-level keys
   */
  private function injectComments(string $yaml): string
  {
    $lines = explode("\n", $yaml);
    $newYaml = "";
    foreach ($lines as $line) {
      foreach (self::getConfigInfo($this->class) as $info) {
        $key = $info["config_info"]["config-key"];
        if (!str_starts_with($line, $key . ":")) continue;
        if ($desc = $info["annotations"]["description"] ?? null) {
          $newYaml .= "# $desc\n";
        }
      }
      $newYaml .= "$line\n";
    }
    return $newYaml;
  }

  /** 
   * Mutates $config with values in $rawData
   * @param IEditable $config
   * @param mixed[] $rawData
   */
  private static function load(IEditable $config, array $rawData, bool $checkValid = true): void
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

    foreach (self::getConfigInfo($className) as $info) {
      $annotations = $info["annotations"];
      $rProp = $info["prop_ref"];
      ["type" => $type, "config-key" => $config_key] = $info["config_info"];

      $config_key = $annotations["config-key"];
      $value = $rawData[$config_key] ?? null;

      if ($type === "object") {
        $class = $annotations["class"] ?? null;
        /** @phpstan-var class-string|null $class */
        if (!($class !== null &&
          class_exists($class) &&
          (new ReflectionClass($class))->implementsInterface(IEditable::class))) {
          throw new TypeError(
            "Invalid PHPDoc in $class: \n" .
              "@class annotation on property " .
              $rProp->getName() . " is not a valid class"
          );
        }

        $rValue = new ReflectionClass($class);
        if ($rValue->implementsInterface(ISubtyped::class) && $rValue->isAbstract()) {
          if (!(is_array($value) && isset($value["subtype"]) && isset($value["options"]))) {
            throw ConfigException::typeMismatch("Keys `subtype` and `options` expected at $config_key");
          }
          $subtypeName = $value["subtype"];
          $class = $class::getSubtypes()[$subtypeName]; // Update $class to subtype
          $value = $value["options"]; // Update $value to point to ISubtyped's data
        }

        /** @phpstan-var class-string<IEditable> $class */
        try {
          $object = new $class;
          self::load($object, is_array($value) ? $value : []);
        } catch (ConfigException $e) {
          if (!(isset($defaults) && isset($defaults[$config_key]))) {
            throw $e;
          } else {
            $object = $defaults[$config_key]; // Use default object when load fails
          }
        }

        $value = $object;
      }

      if ($type === "vector") {
        if (
          is_array($value) &&
          array_values($value) === $value &&
          count($value) >= 3
        ) {
          $value = new Vector3($value[0], $value[1], $value[2]);
        }
      }

      if ($value !== null && self::typeMatches($value, $type)) {
        $rProp->setValue($config, $value);
      } else {
        [$expected, $got] = [$type, gettype($rawData[$config_key] ?? null)];
        Plugin::getInstance()->getLogger()->debug(
          get_class($config) . ": " . $config_key .
            " rejected because of type mis-match. Expected $expected but got $got."
        );
        if (isset($defaults) && isset($defaults[$config_key])) {
          $rProp->setValue($config, $defaults[$config_key]);
        } else {
          throw ConfigException::typeMismatch("Expected $expected but got $got at key $config_key");
        }
      }
    }

    if ($checkValid && $config instanceof IValid && !($result = $config->isValid())->success()) {
      if ($config instanceof IConfig) {
        self::load($config, $config::getDefaults(), false);
        if (!($result = $config->isValid())->success()) {
          throw new TypeError("IConfig's defaults do not pass IValid checks: " . $className);
        }
      } else {
        throw ConfigException::unknownError("Data failed validity check: " . $result->getError());
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

    foreach (self::getConfigInfo($className) as $info) {
      ["type" => $type, "config-key" => $config_key] = $info["config_info"];
      $annotations = $info["annotations"];

      $is_object = $type === "object";
      $is_vector = $type === "vector";

      $rProp = $info["prop_ref"];
      $value = $rProp->getValue($config);

      if ($is_vector && $value instanceof Vector3) {
        $value = [$value->getX(), $value->getY(), $value->getZ()];
      }

      if ($is_object) {
        if (!($value instanceof IEditable)) {
          throw new TypeError("Property {$rProp->getName()} is not a non-null instance of IEditable");
        }
        $data = self::unload($value);
        if ($value instanceof ISubtyped) {
          $subtypedClass = $annotations["class"];
          $subtype = array_search(get_class($value), $subtypedClass::getSubtypes(), true);
          $value = [
            "subtype" => $subtype,
            "options" => $data,
          ];
        } else {
          $value = $data;
        }
      }

      $rawData[$config_key] = $value;
    }
    return $rawData;
  }

  /**
   * @phpstan-param class-string<IEditable> $className
   * @phpstan-return array<string, array{prop_ref: ReflectionProperty, annotations: array<string, string>, config_info: array{type: string, config-key: string}}>
   */
  private static function getConfigInfo(string $className): array
  {
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

      /** @var array<string, string> $annotations */
      $annotations = array_combine($matches[1], $matches[2]);
      $requiredAnnotations = ["type", "config-key"];

      if (!$annotations) continue;

      foreach ($requiredAnnotations as $req) {
        if (!isset($annotations[$req])) {
          unset($props[$rProp->getName()]);
          continue 2;
        }
      }

      $props[$rProp->getName()] = [
        "prop_ref" => $rProp,
        "annotations" => $annotations,
        "config_info" => [
          "type" => $annotations["type"],
          "config-key" => $annotations["config-key"],
        ],
      ];
    }
    return $props;
  }

  private static function typeMatches(mixed $value, string $type): bool
  {
    switch ($type) {
      case "list":
        return is_array($value);
      case "object":
        return is_object($value);
      case "string":
        return is_string($value);
      case "boolean":
        return is_bool($value);
      case "integer":
        return is_int($value);
      case "float":
        return is_float($value);
      case "vector":
        return is_object($value) && $value instanceof Vector3;
      default:
        throw new TypeError("Unsupported Type: $type");
    }
  }
}
