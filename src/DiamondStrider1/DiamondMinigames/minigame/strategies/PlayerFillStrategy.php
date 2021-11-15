<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\data\metadata\ISubtypeProvider;

abstract class PlayerFillStrategy extends BaseStrategy implements ISubtypeProvider
{
  public static function getSubtypes(): array
  {
    static $subtypes = [
      "FFA" => PlayerFillFFA::class,
      "Queued" => PlayerFillQueued::class,
    ];
    return $subtypes;
  }
}
