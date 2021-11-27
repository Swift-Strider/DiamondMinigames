<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use AssertionError;
use Attribute;
use Closure;
use DiamondStrider1\DiamondMinigames\data\ClassInfo;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use pocketmine\form\Form;
use pocketmine\Player;
use ReflectionClass;
use ReflectionProperty;
use TypeError;

/**
 * @phpstan-template T of object
 * @phpstan-implements IValueType<T>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ObjectType implements IValueType
{
  /** @phpstan-var ClassInfo<T> */
  private ClassInfo $classInfo;
  
  /**
   * @phpstan-param class-string<T> $class
   */
  public function __construct(
    private string $class,
    private string $config_key = "<root>",
    private string $description = ""
  ) {
    $this->classInfo = ClassInfo::getInfo($class);
  }

  public function getKey(): string
  {
    return $this->config_key;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function createForm($value, Closure $callback): Form
  {
    if ($this->classInfo->getSubtypes() !== null) {
      return $this->createSubtypeForm($value, $callback);
    }
    return $this->createObjectForm($value, $callback);
  }

  /** 
   * @phpstan-param T|null $value
   * @phpstan-param Closure(T|null): void $callback
   */
  private function createSubtypeForm(?object $value, Closure $callback): Form
  {
    if ($this->classInfo->getSubtypes() === null)
      throw new AssertionError("\$this->subtypes should not be null");
    $options = [];
    $indexToClass = [];
    foreach ($this->classInfo->getSubtypes() as $name => $class) {
      $indexToClass[] = $class;
      $isUsed = $value && (new ReflectionClass($class))->isInstance($value);
      $options[] = new MenuOption(($isUsed ? "§6" : "§8") . "$name");
    }
    return new MenuForm(
      "Configuration",
      $this->description,
      $options,
      function (
        Player $player,
        int $selectedOption
      ) use ($value, $callback, $indexToClass): void {
        $class = $indexToClass[$selectedOption];
        /** @phpstan-var Closure(object|null): void $callback */
        $player->sendForm((new self($class, description: $this->description))
          ->createForm($value, $callback));
      },
      function (Player $player) use ($callback): void {
        ($callback)(null);
      }
    );
  }

  /** 
   * @phpstan-param T|null $value
   * @phpstan-param Closure(T|null): void $callback
   */
  private function createObjectForm(?object $value, Closure $callback, ?string $lastError = null): Form
  {
    if ($value === null || !$this->classInfo->isInstanceOf($value)) $value = new $this->class;
    foreach ($this->classInfo->getProps() as [$rProp, $inject]) {
      /** @var ReflectionProperty $rProp */
      /** @var IValueType $inject */
      $valueString = $inject->shortString($rProp->isInitialized($value) ? $rProp->getValue($value) : null);
      $options[] = new MenuOption("{$inject->getKey()} is $valueString");
    }
    $options[] = new MenuOption("Submit");
    $option_count = count($options);
    return new MenuForm(
      "Configure Properties",
      "Choose an option to get started" . ($lastError !== null ? "\n\n§4$lastError" : ""),
      $options,
      function (
        Player $player,
        int $selectedOption
      ) use ($callback, $value, $option_count): void {
        if ($selectedOption === $option_count - 1) {
          foreach ($this->classInfo->getProps() as [$rProp]) {
            if ($rProp->getValue($value) === null) {
              $player->sendForm($this->createObjectForm(
                $value,
                $callback,
                "You have not filled all properties!"
              ));
              return;
            }
          }
          if ($value instanceof IValidationProvider) {
            if (!($result = $value->isValid())->success()) {
              /** @phpstan-var T $value */
              $player->sendForm($this->createObjectForm(
                $value,
                $callback,
                $result->getError()
              ));
              return;
            }
          }
          /** @phpstan-var T $value */
          ($callback)($value);
          return;
        }
        [$rProp, $inject] = $this->classInfo->getProps()[$selectedOption];
        /** @var ReflectionProperty $rProp */
        /** @var IValueType $inject */
        $player->sendForm($inject->createForm(
          $rProp->isInitialized($value) ? $rProp->getValue($value) : null,
          function ($propValue) use ($value, $callback, $player, $rProp): void {
            if ($propValue !== null) $rProp->setValue($value, $propValue);
            $player->sendForm($this->createObjectForm($value, $callback));
          }
        ));
      },
      function (Player $player) use ($value, $callback): void {
        $player->sendForm(new ModalForm(
          "Abandon all data?",
          "Your changes will be lost!",
          function (Player $player, bool $choice) use ($value, $callback): void {
            if ($choice) ($callback)(null);
            else $player->sendForm($this->createObjectForm($value, $callback));
          }
        ));
      }
    );
  }

  public function shortString(mixed $value): string
  {
    if (!is_object($value)) return "NOT SET";
    $class = get_class($value);

    $slashPos = strrpos($class, "\\", -1);
    $shortClass = substr($class, $slashPos !== false ? $slashPos + 1 : 0);
    $prettyClass = ucfirst($shortClass);
    return "{$prettyClass} {...}";
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    if (!is_object($value)) throw new TypeError("\$value must be an object.");
    $padding = str_repeat("  ", $context->getDepth());
    $props = ClassInfo::getInfo(get_class($value))->getProps();

    if (count($props) === 0 && $context->getDepth() !== 0)
      $lines = "[]";
    else
      $lines = $context->getDepth() === 0 ? "" : "\n";

    $subtypes = $this->classInfo->getSubtypes();
    if ($subtypes !== null) {
      $value_class = get_class($value);
      $subtype = null;
      foreach ($subtypes as $name => $class) {
        if ($class == $value_class) $subtype = $name;
      }
      if (!$subtype) throw new TypeError("No \$subtype found");
      $lines = "\n{$padding}# valid subtypes are: " . implode(', ', array_keys($subtypes)) . "\n";
      $lines .= "{$padding}subtype: $subtype\n";
      $lines .= "{$padding}options: " . (count($props) > 0 ? "\n" : "[]");
      $padding .= "  ";
      $context = $context->addKey("options");
    }

    foreach ($props as [$rProp, $inject]) {
      /** @var ReflectionProperty $rProp */
      /** @var IValueType $inject */
      $newContext = $context->addKey($inject->getKey());
      $valueLines = rtrim($inject->yamlLines($rProp->getValue($value), $newContext));

      foreach (explode("\n", $inject->getDescription()) as $descLine) {
        $lines .= "$padding# $descLine\n";
      }
      $lines .= "$padding{$inject->getKey()}: $valueLines\n";
    }

    return $lines;
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_array($raw)) throw new ConfigException("Expected key pair values", $context);
    if (($subs = $this->classInfo->getSubtypes()) !== null) {
      if (!isset($raw["subtype"]) || !isset($raw["options"]))
        throw new ConfigException("Expected keys \"subtype\" and \"options\"", $context);
      if (($sub = $subs[$raw["subtype"]] ?? null) === null) {
        throw new ConfigException(
          "Unknown \"subtype\"; Accepted are: " . implode(", ", array_keys($subs)),
          $context
        );
      }

      return (new ObjectType($sub))->fromRaw($raw["options"], $context->addKey("options"));
    }
    $object = new $this->class;
    foreach ($this->classInfo->getProps() as [$rProp, $inject]) {
      /** @var ReflectionProperty $rProp */
      /** @var IValueType $inject */
      if (($propValue = $raw[$inject->getKey()] ?? null) === null) {
        if (($defaults = $this->classInfo->getDefaults()) === null)
          throw new ConfigException("Property Key \"{$inject->getKey()}\" is missing.", $context);
        $parsed = $defaults[$inject->getKey()];
      } else {
        $parsed = $inject->fromRaw($propValue, $context->addKey($inject->getKey()));
      }
      $rProp->setValue($object, $parsed);
    }
    if ($object instanceof IValidationProvider) {
      if (!($res = $object->isValid())->success())
        throw new ConfigException($res->getError(), $context);
    }
    /** @phpstan-var T $object */
    return $object;
  }
}
