<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use DiamondStrider1\DiamondMinigames\data\metadata\IDefaultProvider;
use DiamondStrider1\DiamondMinigames\data\metadata\ISubtypeProvider;
use DiamondStrider1\DiamondMinigames\data\metadata\IValueType;
use DiamondStrider1\DiamondMinigames\data\metadata\ListType;
use ReflectionClass;
use TypeError;

/**
 * @template T of object
 */
class ClassInfo
{
  /** @var self[] */
  private static array $cache = [];
  
  /** 
   * @phpstan-template V of object
   * @phpstan-param class-string<V> $class
   * @phpstan-return self<V>
   */
  public static function getInfo(string $class): self
  {
    if (isset(self::$cache[$class])) {
      $classInfo = self::$cache[$class];
    } else {
      $classInfo = self::$cache[$class] = new self($class);
    }
    /** @phpstan-var self<V> $classInfo */
    return $classInfo;
  }

  /** @phpstan-var class-string<T>[] */
  private array $subtypes;
  /** @phpstan-var array{\ReflectionProperty, IValueType}[] $props */
  private array $props = [];
  /** @phpstan-var array<string, mixed> */
  private array $defaults;
  /** @phpstan-var ReflectionClass<T> */
  private ReflectionClass $reflection;

  /** @phpstan-param class-string<T> $class */
  private function __construct(string $class)
  {
    $this->reflection = new ReflectionClass($class);
    if ($this->reflection->isAbstract()) {
      if (!$this->reflection->implementsInterface(ISubtypeProvider::class))
        throw new TypeError("Abstract Class does not implement ISubtypeProvider");
      /** @phpstan-var class-string<T>[] $subtypes */
      $subtypes = $this->reflection->getMethod("getSubtypes")->invoke(null);
      $this->subtypes = $subtypes;
      return;
    }
    foreach ($this->reflection->getProperties() as $rProp) {
      if ($rProp->isStatic()) continue;
      $inject = null;
      foreach ($rProp->getAttributes() as $attr) {
        $rAttr = new ReflectionClass($attr->getName());
        if (
          $rAttr->isSubclassOf(IValueType::class) &&
          $rAttr->getName() !== ListType::class
        ) {
          $listType = $rProp->getAttributes(ListType::class)[0] ?? null;
          if ($listType) {
            /** @var IValueType $other */
            $other = $attr->newInstance();
            $inject = $listType->newInstance();
            /** @var ListType $inject */
            $inject->setType($other);
          } else {
            $inject = $attr->newInstance();
          }
          break;
        }
      }
      if ($inject === null) continue;
      /** @var IValueType $inject */
      $this->props[] = [$rProp, $inject];
    }
    if ($this->reflection->implementsInterface(IDefaultProvider::class)) {
      /** @phpstan-var array<string, mixed> $defaults */
      $defaults = $this->reflection->getMethod("getDefaults")->invoke(null);
      $this->defaults = $defaults;
    }
  }

  /** @phpstan-return class-string<T>[]|null */
  public function getSubtypes(): ?array
  {
    return isset($this->subtypes) ? $this->subtypes : null;
  }

  /** @phpstan-return array{\ReflectionProperty, IValueType}[] $props */
  public function getProps(): array
  {
    return $this->props;
  }

  /** @phpstan-return array<string, mixed>|null */
  public function getDefaults(): ?array
  {
    return isset($this->defaults) ? $this->defaults : null;
  }

  public function isInstanceOf(mixed $value): bool {
    if (!is_object($value)) return false;
    return $this->reflection->isInstance($value);
  }
}
