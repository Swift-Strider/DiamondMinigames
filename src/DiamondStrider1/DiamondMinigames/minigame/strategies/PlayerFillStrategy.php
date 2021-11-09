<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\types\ISubtyped;

abstract class PlayerFillStrategy extends BaseStrategy implements ISubtyped
{
  /** @phpstan-var array<string, class-string<self>> */
  private static array $subtypes = [
    "ffa" => PlayerFillFFA::class,
    "queued" => PlayerFillQueued::class,
  ];

  public static function getSubtypes(): array
  {
    return self::$subtypes;
  }
}
