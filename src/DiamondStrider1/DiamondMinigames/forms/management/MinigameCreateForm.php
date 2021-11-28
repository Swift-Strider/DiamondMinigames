<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\management;

use Closure;
use DiamondStrider1\DiamondMinigames\data\metadata\ObjectType;
use DiamondStrider1\DiamondMinigames\data\MinigameStore;
use DiamondStrider1\DiamondMinigames\minigame\MinigameBlueprint;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use pocketmine\Player;

class MinigameCreateForm
{
  /** @phpstan-param Closure(?MinigameBlueprint $mg): void $callback */
  public function __construct(
    private Closure $callback,
    private ?MinigameBlueprint $default = null
  ) {
  }

  public function sendTo(Player $player): void
  {
    $formDescription = "Configure your Minigame";
    $player->sendForm(
      (new ObjectType(MinigameBlueprint::class, $formDescription))->createForm(
        $this->default,
        $this->callback
      )
    );
  }
}
