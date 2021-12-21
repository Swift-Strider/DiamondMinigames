<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use DiamondStrider1\DiamondMinigames\data\metadata\IValidationProvider;
use DiamondStrider1\DiamondMinigames\data\metadata\ObjectType;
use DiamondStrider1\DiamondMinigames\data\metadata\StringType;
use DiamondStrider1\DiamondMinigames\data\metadata\WorldTemplateType;
use DiamondStrider1\DiamondMinigames\data\MinigameStore;
use DiamondStrider1\DiamondMinigames\data\WorldTemplate;
use DiamondStrider1\DiamondMinigames\minigame\impl\BasePlayerFillImpl;
use DiamondStrider1\DiamondMinigames\minigame\impl\IStrategyImpl;
use DiamondStrider1\DiamondMinigames\minigame\strategies\PlayerFillStrategy;
use DiamondStrider1\DiamondMinigames\minigame\strategies\WinStrategy;
use DiamondStrider1\DiamondMinigames\misc\Result;

class MinigameBlueprint implements IValidationProvider
{
  #[WorldTemplateType("world", "The template world is copied for players to join")]
  public WorldTemplate $world;
  #[StringType("name", "The pretty name for the minigame")]
  public string $name;
  #[ObjectType(
    PlayerFillStrategy::class,
    config_key: "player-fill-strategy",
    description: "Determines when players may join a game"
  )]
  public PlayerFillStrategy $playerFillStrategy;
  #[ObjectType(
    WinStrategy::class,
    config_key: "win-strategy",
    description: "Determines how players may (or may not) win"
  )]
  public WinStrategy $winStrategy;

  public function isValid(): Result
  {
    $errors = [];

    if (!MinigameStore::checkValidName($this->name))
      $errors[] = "The name must only contain Alphanumerics and '_'.";

    if (count($errors) > 0) {
      return Result::error(implode(", AND ", $errors));
    }
    return Result::ok();
  }

  /** @phpstan-return array{BasePlayerFillImpl, IStrategyImpl[]} */
  public function buildStrategies(): array
  {
    return [
      $this->playerFillStrategy->createImpl(),
      [$this->winStrategy->createImpl()]
    ];
  }
}
