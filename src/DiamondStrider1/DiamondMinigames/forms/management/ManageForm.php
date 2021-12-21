<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\management;

use DiamondStrider1\DiamondMinigames\misc\MainConfig;
use DiamondStrider1\DiamondMinigames\data\metadata\ObjectType;
use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\Plugin;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\form\Form;
use pocketmine\player\Player;

class ManageForm extends BaseForm
{
  private ?string $notice = null;

  protected function createForm(Player $player): Form
  {
    return new MenuForm(
      "Manage DiamondMinigames",
      "This form manipulates DiamondMinigames's Configurations!" .
        ($this->notice ? "\n§3" . $this->notice : ""),
      [
        new MenuOption("Manage Minigames"),
        new MenuOption("Manage Config"),
        new MenuOption("Reload Plugin"),
      ],
      function (Player $player, int $selectedOption): void {
        if (!$player->hasPermission("diamondminigames.manage")) return;
        $this->notice = null;
        switch ($selectedOption) {
          case 0:
            $this->openForm($player, new MinigamesForm);
            break;
          case 1:
            $currentConfig = Plugin::getInstance()->getMainConfig();
            $formDescription = "This will be saved to config.yml immediately";
            $player->sendForm((new ObjectType(MainConfig::class, $formDescription))->createForm(
              $currentConfig,
              function ($value) use ($player): void {
                if ($value) Plugin::getInstance()->setMainConfig($value);
                $this->notice = "Saved settings to config.yml";
                $this->sendTo($player);
              }
            ));
            break;
          case 2:
            $time = microtime(true);
            Plugin::getInstance()->reloadPlugin();
            $diff = microtime(true) - $time;
            $this->notice = sprintf("Plugin Reloaded §7[§2%.2fs§7]", $diff);
            $this->sendTo($player);
        }
      }
    );
  }
}
