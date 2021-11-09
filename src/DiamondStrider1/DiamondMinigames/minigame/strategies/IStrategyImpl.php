<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\minigame\Minigame;

/**
 * A class implementing this can subscribe to
 * minigame hooks that are fired on a minigame.
 * 
 * In order for a function to be registered for a hook
 * it must take 1 argument that is an instanceof BaseHook
 */
interface IStrategyImpl
{
  public function onInit(Minigame $minigame): void;
  public function onDestroy(): void;
}
