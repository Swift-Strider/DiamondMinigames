<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\management;

use Closure;
use DiamondStrider1\DiamondMinigames\data\MinigameStore;
use DiamondStrider1\DiamondMinigames\forms\edit\ObjectForm;
use DiamondStrider1\DiamondMinigames\forms\edit\StringForm;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use DiamondStrider1\DiamondMinigames\minigame\MinigameBlueprint;
use pocketmine\Player;

class MinigameCreateForm
{
  /** @phpstan-param Closure(string, MinigameBlueprint): void $callback */
  public function __construct(
    private Closure $callback,
    private ?MinigameBlueprint $default = null,
    private ?string $defaultName = null,
  ) {
  }

  public function sendTo(Player $player): void
  {
    $editor = new ObjectForm([
      "label" => "Manage Minigames",
      "description" => "Changes are saved to config.yml immediately after the form is submitted.",
      "class" => MinigameBlueprint::class,
    ], $this->default);
    $editor->onFinish(function ($value) use ($player): void {
      if (!$value) {
        FormSessions::sendPrevious($player);
        return;
      }
      $editor = new StringForm([
        "label" => "Minigame Name",
        "description" => "A unique name to identify your minigame." .
          "Must start with a letter and consist of alphanumerics, underscores and spaces",
      ], $this->defaultName);
      $editor->onFinish(function ($name) use ($player, $value): void {
        if (!$player->hasPermission("diamondminigames.manage")) return;

        if ($name === null || !MinigameStore::checkValidName($name)) {
          $name = "#" . random_int(1000, 9999);
        }

        ($this->callback)($name, $value);
      });
      $editor->sendTo($player);
    }, false);
    $editor->sendTo($player);
  }
}
