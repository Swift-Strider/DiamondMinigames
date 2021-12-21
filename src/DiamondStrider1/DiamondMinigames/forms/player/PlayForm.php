<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\player;

use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\Plugin;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use pocketmine\form\Form;

class PlayForm extends BaseForm
{
  private ?string $notice = null;

  protected function createForm(Player $player): Form
  {
    $mgStore = Plugin::getInstance()->getMinigameStore();
    $indexToName = [];
    $options = [];
    foreach ($mgStore->getAll() as $mg) {
      $indexToName[] = $mg->name;
      $options[] = new MenuOption("§2{$mg->name}");
    }
    return new MenuForm(
      "Play Minigame",
      "§c" . ($this->notice ?? ""),
      $options,
      function (Player $player, int $selectedOption) use ($mgStore, $indexToName): void {
        $name = $indexToName[$selectedOption];
        if ($mgStore->get($name) === null) {
          $this->notice = "That minigame no longer exists :(";
          $this->sendTo($player);
          return;
        }
        if (!Plugin::getInstance()->getMinigameManager()->send($player, $name)) {
          $this->notice = "Could not send you to that game :(";
        }
      }
    );
  }
}
