<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\types;

interface IConfig
{
  /**
   * @return array<string, mixed>
   */
  public static function getDefaults(): array;
}
