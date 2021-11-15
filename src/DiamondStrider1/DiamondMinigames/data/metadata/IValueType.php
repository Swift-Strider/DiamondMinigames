<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Closure;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use pocketmine\form\Form;

/**
 * @phpstan-template T
 * An attribute that gives info about a property
 */
interface IValueType {
  public function getKey(): string;
  public function getDescription(): string;
  /**
   * @phpstan-param T|null $value
   * @phpstan-param Closure(T|null): void $callback
   */
  public function createForm($value, Closure $callback): Form;
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
