<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\attributes;

use Attribute;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;

/**
 * @phpstan-implements IValueType<int>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IntType implements IValueType
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
        if (!is_int($value)) return "NOT SET";
        return "$value";
    }

    public function yamlLines(mixed $value, ConfigContext $context): string
    {
        return (string) $value;
    }

    public function fromRaw(mixed $raw, ConfigContext $context): mixed
    {
        if (!is_int($raw)) throw new ConfigException("Expected integer", $context);
        return $raw;
    }
}
