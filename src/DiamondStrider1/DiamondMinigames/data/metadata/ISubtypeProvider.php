<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

interface ISubtypeProvider
{
  /** @phpstan-return array<string, class-string<static>> */
  public static function getSubtypes(): array;
}
