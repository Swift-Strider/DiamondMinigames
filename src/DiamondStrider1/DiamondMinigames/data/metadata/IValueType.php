<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use DiamondStrider1\DiamondMinigames\data\ConfigContext;

/**
 * @phpstan-template T
 * An attribute that gives info about a property
 */
interface IValueType {
  public function getKey(): string;
  public function getDescription(): string;
  public function shortString(mixed $value): string;
  /**
   * @phpstan-param T $value
   */
  public function yamlLines(mixed $value, ConfigContext $context): string;
  /**
   * @phpstan-return T
   */
  public function fromRaw(mixed $raw, ConfigContext $context): mixed;
}
