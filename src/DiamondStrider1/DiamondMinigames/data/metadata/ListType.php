<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Attribute;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use TypeError;

/**
 * @phpstan-template T
 * @phpstan-implements IValueType<array<int, T>>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ListType implements IValueType
{
  /** @phpstan-var IValueType<T> */
  private IValueType $type;

  public function __construct(
    private string $config_key = "<root>",
    private string $description = ""
  ) {
  }

  public function setType(IValueType $type): void
  {
    $this->type = $type;
  }

  public function getKey(): string
  {
    return $this->config_key;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function shortString(mixed $value): string
  {
    if (!is_array($value)) return "NOT SET";
    return "List [...]";
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    if (!(is_array($value) && array_values($value) === $value))
      throw new TypeError("\$value must be an array-list");
    $lines = "\n";
    foreach ($value as $i => $v) {
      $newContext = $context->addKey($i);
      $valueLines = rtrim($this->type->yamlLines($v, $newContext));
      $padding = str_repeat("  ", $context->getDepth());
      $lines .= "$padding - $valueLines\n";
    }
    if ($lines === "\n") return "[]";
    return $lines;
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_array($raw)) throw new ConfigException("Expected key pair values", $context);
    /** @phpstan-var array<int, T> */
    $array = [];
    foreach ($raw as $i => $value) {
      $array[] = $this->type->fromRaw($value, $context->addKey($i));
    }
    return $array;
  }
}
