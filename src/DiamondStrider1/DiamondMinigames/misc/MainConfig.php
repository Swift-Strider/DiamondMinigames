<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\misc;

use DiamondStrider1\DiamondMinigames\data\metadata\IDefaultProvider;

class MainConfig implements IDefaultProvider
{
  /** @phpstan-var array<string, mixed> */
  private static array $defaults;
  
  public static function getDefaults(): array
  {
    if (!isset(self::$defaults)) {
      self::$defaults = [];
    }

    return self::$defaults;
  }
}
