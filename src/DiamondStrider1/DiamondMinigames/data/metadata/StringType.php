<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Attribute;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;

/**
 * @phpstan-implements IValueType<string>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class StringType implements IValueType
{
  public function __construct(
    private string $config_key,
    private string $description
  ) {
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
    if (!is_string($value)) return "NOT SET";
    return "\"$value\"";
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    $value = str_replace(["\n", "\r"], ['\n', '\r'], (string) $value);
    return "\"$value\"";
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_string($raw)) throw new ConfigException("Expected string", $context);
    return $raw;
  }
}
