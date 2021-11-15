<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Attribute;

/**
 * @phpstan-template T
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class InjectValue
{
  /**
   * @phpstan-param IValueType<T> $type
   */
  public function __construct(
    private IValueType $type,
    private string $config_key,
    private string $description
  ) {
  }

  /**
   * @phpstan-return IValueType<mixed>
   */
  public function getType(): IValueType
  {
    return $this->type;
  }

  public function getKey(): string
  {
    return $this->config_key;
  }

  public function getDescription(): string
  {
    return $this->description;
  }
}
