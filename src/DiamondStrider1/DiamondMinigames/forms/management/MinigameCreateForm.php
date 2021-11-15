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
  /** @phpstan-param Closure(?MinigameBlueprint $mg, ?string $name): void $callback */
  public function __construct(
    private Closure $callback,
    private ?MinigameBlueprint $default = null,
    private ?string $defaultName = null,
    private bool $promptName = true
  ) {
  }

  public function sendTo(Player $player): void
  {
    $formDescription = "Configure your Minigame";
    $player->sendForm(
      (new ObjectType(MinigameBlueprint::class, $formDescription))->createForm(
        $this->default,
        function ($value) use ($player): void {
          if (!$value) {
            ($this->callback)(null, null);
            return;
          }
          if (!$this->promptName) {
            ($this->callback)($value, null);
            return;
          }
          $player->sendForm(new CustomForm(
            "Minigame's Name",
            [
              new Label("description", "This is also the filename the minigame is saved " .
                "to, so only alphanumerics and underscores are allowed"),
              new Input("name", "", "", $this->defaultName ?? ""),
            ],
            function (Player $player, CustomFormResponse $data) use ($value): void {
              $name = $data->getString("name");
              if (!MinigameStore::checkValidName($name)) {
                $name = "#" . random_int(1000, 9999);
              }
              ($this->callback)($value, $name);
            },
            function (Player $player) use ($value): void {
              ($this->callback)($value, "#" . random_int(1000, 9999));
            }
          ));
        }
      )
    );
  }
}
