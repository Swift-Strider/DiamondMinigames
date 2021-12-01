<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\Minigame;

/**
 * A state-ful implementation of a strategy
 * that can subscribe to minigame events
 * by implementing \pocketmine\event\Listener
 */
interface IStrategyImpl
{
  public function onInit(Minigame $minigame): void;
  public function onDestroy(): void;
}
