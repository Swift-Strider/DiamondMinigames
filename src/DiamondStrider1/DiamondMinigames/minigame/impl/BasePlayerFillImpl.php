<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\MGPlayer;
use DiamondStrider1\DiamondMinigames\minigame\Team;
use DiamondStrider1\DiamondMinigames\misc\Result;
use pocketmine\Player;

abstract class BasePlayerFillImpl implements IStrategyImpl
{
  /** @phpstan-return array{Result, ?Team} */
  abstract public function addPlayer(MGPlayer $player): array;
  abstract public function removePlayer(MGPlayer $player): void;
}
