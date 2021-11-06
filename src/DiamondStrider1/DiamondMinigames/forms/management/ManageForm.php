<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\management;

use DiamondStrider1\DiamondMinigames\data\MainConfig;
use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\forms\edit\ObjectForm;
use DiamondStrider1\DiamondMinigames\minigame\MinigameBlueprint;
use DiamondStrider1\DiamondMinigames\Plugin;
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
      "This form manipulates DiamondMinigames's Configurations!",
      [
        new MenuOption("Manage Minigames"),
        new MenuOption("Manage Config"),
      ],
      function (Player $player, int $selectedOption): void {
        if (!$player->hasPermission("diamondminigames.manage")) return;
        switch ($selectedOption) {
          case 0:
            // TODO: minigame management form
            break;
          case 1:
            $editor = new ObjectForm([
              "label" => "Edit Configuration",
              "description" => "Changes are saved to config.yml immediately after the form is submitted.",
              "class" => MainConfig::class,
            ], Plugin::getInstance()->getMainConfig());
            $editor->onFinish(function ($value): void {
              if ($value) Plugin::getInstance()->setMainConfig($value);
            });
            $this->openForm($player, $editor);
            break;
        }
      }
    );
  }
}
