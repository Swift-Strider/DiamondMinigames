<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Attribute;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use pocketmine\math\Vector3;
use TypeError;

/**
 * @phpstan-implements IValueType<Vector3>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class VectorType implements IValueType
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
    if (!($value instanceof Vector3)) return "NOT SET";
    return sprintf("(x: %.2f, y: %.2f, z: %.2f)", $value->x, $value->y, $value->z);
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    if (!($value instanceof Vector3))
      throw new TypeError("\$value is not a Vector3");
    return sprintf("[%.6f, %.6f, %.6f]", $value->x, $value->y, $value->z);
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_array($raw) || count($raw) < 3)
      throw new ConfigException("Expected Vector3 (list of 3 numbers)", $context);
    return new Vector3((float) $raw[0], (float) $raw[1], (float) $raw[2]);
  }
}
