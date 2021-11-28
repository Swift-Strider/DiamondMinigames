<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use DiamondStrider1\DiamondMinigames\data\metadata\ObjectType;
use DiamondStrider1\DiamondMinigames\minigame\impl\IStrategyImpl;
use DiamondStrider1\DiamondMinigames\minigame\strategies\PlayerFillStrategy;

class MinigameBlueprint
{
  #[ObjectType(
    PlayerFillStrategy::class,
    config_key: "player-fill-strategy",
    description: "Determines when players may join a game"
  )]
  public PlayerFillStrategy $playerFillStrategy;

  /** @return IStrategyImpl[] */
  public function buildStrategies(): array
  {
    return [
      $this->playerFillStrategy->createImpl(),
    ];
  }
}
