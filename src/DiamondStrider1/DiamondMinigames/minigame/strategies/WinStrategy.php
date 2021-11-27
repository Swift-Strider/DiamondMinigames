<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\data\metadata\ISubtypeProvider;

abstract class WinStrategy extends BaseStrategy implements ISubtypeProvider
{
  public static function getSubtypes(): array
  {
    static $subtypes = [
      "LastTeam" => LastTeamWin::class,
      "None" => NoWin::class,
    ];
    return $subtypes;
  }
}
