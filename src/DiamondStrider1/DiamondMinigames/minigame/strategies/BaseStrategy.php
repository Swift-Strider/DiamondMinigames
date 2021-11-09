<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\types\IEditable;
use DiamondStrider1\DiamondMinigames\types\IValid;
use DiamondStrider1\DiamondMinigames\types\Result;

abstract class BaseStrategy implements IEditable, IValid
{
  abstract public function createImpl(): IStrategyImpl;
  public function isValid(): Result
  {
    return Result::ok();
  }
}
