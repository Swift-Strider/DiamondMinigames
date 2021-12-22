<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\data\metadata\IValidationProvider;
use DiamondStrider1\DiamondMinigames\data\metadata\ListType;
use DiamondStrider1\DiamondMinigames\data\metadata\VectorType;
use DiamondStrider1\DiamondMinigames\minigame\impl\PlayerFillFFAImpl;
use DiamondStrider1\DiamondMinigames\misc\Result;
use pocketmine\math\Vector3;

class PlayerFillFFA extends PlayerFillStrategy implements IValidationProvider
{
  /** @var Vector3[] */
  #[ListType("spawns", "Randomly selected player spawns")]
  #[VectorType("", "Randomly selected player spawns")]
  public array $spawns;

  public function isValid(): Result
  {
    if (count($this->spawns) === 0) {
      return Result::error("At least one spawn is required");
    }
    return Result::ok();
  }

  public function createImpl(): PlayerFillFFAImpl
  {
    return new PlayerFillFFAImpl($this);
  }
}
