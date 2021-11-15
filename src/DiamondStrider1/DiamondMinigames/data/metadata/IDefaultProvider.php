<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

interface IDefaultProvider
{
  /** @phpstan-return array<string, mixed> */
  public static function getDefaults(): array;
}
