<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\management;

use DiamondStrider1\DiamondMinigames\data\MinigameStore;
use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\forms\edit\StringForm;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use DiamondStrider1\DiamondMinigames\minigame\MinigameBlueprint;
use DiamondStrider1\DiamondMinigames\Plugin;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use pocketmine\form\Form;
use pocketmine\Player;

class MinigamesForm extends BaseForm
{
  private ?string $notice = null;
  protected function createForm(Player $player): Form
  {
    $options = [];
    $minigames = Plugin::getInstance()->getMinigameStore()->getMinigames();
    $indexToName = [];
    foreach (array_keys($minigames) as $name) {
      $indexToName[] = $name;
      $options[] = new MenuOption("§8" . $name);
    }
    $options[] = new MenuOption("§2Create A New Minigame");
    $option_count = count($options);
    return new MenuForm(
      "Server Minigames",
      "Create, View, Edit, and Delete Minigames on the server" .
        ($this->notice !== null ? "\n§3" . $this->notice : ""),
      $options,
      function (Player $player, int $selectedOption) use ($option_count, $indexToName): void {
        $this->notice = null;
        if ($selectedOption === $option_count - 1) {
          $editor = new MinigameCreateForm(
            function (string $name, MinigameBlueprint $minigame) {
              $mgStore = Plugin::getInstance()->getMinigameStore();
              if (isset($mgStore->getMinigames()[$name])) {
                $oldName = $name;
                $name .= "_" . random_int(1000, 9999);
                $this->notice = "A minigame named $oldName exists, so $name has been used instead.";
              } else {
                $this->notice = "Created Minigame $name";
              }
              $mgStore->setMinigame($name, $minigame);
            }
          );
          $editor->sendTo($player);
          FormSessions::pushPrevious($player, $this);
          return;
        }

        $name = $indexToName[$selectedOption];
        if (!(Plugin::getInstance()->getMinigameStore()->getMinigames()[$name] ?? null)) {
          $this->notice = "Minigame ($name) No Longer Exists";
          $this->sendTo($player);
          return;
        }

        $player->sendForm(new MenuForm(
          "Options for $name",
          "What do you want to do?",
          [
            new MenuOption("§2Edit and Rename"),
            new MenuOption("§2Copy"),
            new MenuOption("§cDelete"),
          ],
          function (Player $player, int $selectedOption) use ($name): void {
            $mg = Plugin::getInstance()->getMinigameStore()->getMinigames()[$name] ?? null;
            if (!$mg) {
              $this->notice = "Minigame ($name) No Longer Exists";
              $this->sendTo($player);
              return;
            }
            switch ($selectedOption) {
              case 0:
                $editor = new MinigameCreateForm(
                  function (string $newName, MinigameBlueprint $minigame) use ($name) {
                    $mgStore = Plugin::getInstance()->getMinigameStore();
                    $mgStore->setMinigame($name, $minigame);

                    $this->notice = "";
                    if ($name !== $newName) {
                      $mgStore->deleteMinigame($name);
                      $this->notice .= "Renamed and ";
                    }
                    $this->notice .= "Updated Minigame $name";
                  },
                  $mg,
                  $name
                );
                $editor->sendTo($player);
                FormSessions::pushPrevious($player, $this);
                break;
              case 1:
                $editor = new StringForm([
                  "label" => "Minigame Name",
                  "description" => "Choose a name for the Minigame copy"
                ]);
                $editor->onFinish(function ($copyName) use ($name, $mg): void {
                  if ($copyName === null || !MinigameStore::checkValidName($copyName)) {
                    $this->notice = "Copy canceled because the name \"$copyName\" was invalid.";
                    return;
                  }
                  $mgStore = Plugin::getInstance()->getMinigameStore();
                  $mgStore->setMinigame($copyName, $mg);
                  $this->notice = "Copied ($name) into ($copyName)";
                });
                $this->openForm($player, $editor);
                break;
              case 2:
                $player->sendForm(new ModalForm(
                  "Delete the minigame $name?",
                  "The minigame will be gone forever (A really long time)",
                  function (Player $player, bool $choice) use ($name): void {
                    if (!$choice) {
                      $this->sendTo($player);
                      return;
                    }
                    $mgStore = Plugin::getInstance()->getMinigameStore();
                    $mgStore->deleteMinigame($name);
                    $this->notice = "Deleted minigame $name. This action cannot be undone.";
                    $this->sendTo($player);
                  }
                ));
            }
          },
          function (Player $player): void {
            $this->sendTo($player);
          }
        ));
      },
      function (Player $player): void {
        FormSessions::sendPrevious($player);
      }
    );
  }
}
