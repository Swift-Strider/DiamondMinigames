<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use DiamondStrider1\DiamondMinigames\minigame\strategies\IStrategyImpl;
use DiamondStrider1\DiamondMinigames\minigame\strategies\PlayerFillStrategy;
use DiamondStrider1\DiamondMinigames\types\IEditable;

class MinigameBlueprint implements IEditable
{
  /**
   * @type object
   * @class DiamondStrider1\DiamondMinigames\minigame\strategies\PlayerFillStrategy
   * @config-key player-fill-strategy
   * @label Player Fill Strategy
   * @description Determines when players may join a game
   */
  public PlayerFillStrategy $playerFillStrategy;

  /** @return IStrategyImpl[] */
  public function buildStrategies(): array
  {
    return [
      $this->playerFillStrategy->createImpl(),
    ];
  }
}
