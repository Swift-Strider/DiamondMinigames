<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\management;

use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\forms\edit\EditForm;
use DiamondStrider1\DiamondMinigames\types\Dummy;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\form\Form;
use pocketmine\Player;

class ManageForm extends BaseForm
{
  protected function createForm(Player $player): Form
  {
    return new MenuForm(
      "Manage DiamondMinigames",
      "Use this form to manipulate DiamondMinigames's Configs",
      [
        new MenuOption("Manage Minigames"),
        new MenuOption("Manage Config"),
      ],
      function (Player $player, int $selectedOption): void {
        if (!$player->hasPermission("diamondminigames.manage")) return;
        switch ($selectedOption) {
        }
      }
    );
  }
}
