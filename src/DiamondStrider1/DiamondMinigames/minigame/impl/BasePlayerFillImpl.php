<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\MGPlayer;
use DiamondStrider1\DiamondMinigames\misc\Result;

abstract class BasePlayerFillImpl implements IStrategyImpl
{
  /** @phpstan-return Result */
  abstract public function addPlayer(MGPlayer $player): Result;
  abstract public function removePlayer(MGPlayer $player): void;
}
