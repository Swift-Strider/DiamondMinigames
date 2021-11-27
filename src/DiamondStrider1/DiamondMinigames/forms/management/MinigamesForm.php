<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\management;

use DiamondStrider1\DiamondMinigames\data\MinigameStore;
use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use DiamondStrider1\DiamondMinigames\minigame\MinigameBlueprint;
use DiamondStrider1\DiamondMinigames\Plugin;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
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
            function (?MinigameBlueprint $minigame, ?string $name) use ($player) {
              if (!$minigame) {
                $this->notice = "Minigame Create Canceled";
                $this->sendTo($player);
                return;
              }
              $name ??= "_" . random_int(1000, 9999);
              $mgStore = Plugin::getInstance()->getMinigameStore();
              if (isset($mgStore->getMinigames()[$name])) {
                $oldName = $name;
                $name .= "_" . random_int(1000, 9999);
                $this->notice = "A minigame named \"$oldName\" exists, so \"$name\" has been used instead.";
              } else {
                $this->notice = "Created Minigame \"$name\"";
              }
              $mgStore->setMinigame($name, $minigame);
              $this->sendTo($player);
            }
          );
          $editor->sendTo($player);
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
            new MenuOption("§2Rename"),
            new MenuOption("§2Edit"),
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
                $player->sendForm(new CustomForm(
                  "Rename Minigame \"$name\"",
                  [
                    new Label("description", "Choose a name for the Minigame copy"),
                    new Input("name", "", "", $name),
                  ],
                  function (Player $player, CustomFormResponse $data) use ($name, $mg): void {
                    $newName = $data->getString("name");
                    if (!MinigameStore::checkValidName($newName) || $newName === $name) {
                      $this->notice = "Rename canceled because the name \"$newName\" was invalid.";
                      $this->sendTo($player);
                      return;
                    }
                    $mgStore = Plugin::getInstance()->getMinigameStore();
                    $mgStore->deleteMinigame($name);
                    $mgStore->setMinigame($newName, $mg);
                    $this->notice = "Renamed \"$name\" to \"$newName\"";
                    $this->sendTo($player);
                  },
                  function (Player $player): void {
                    $this->notice = "Rename Canceled";
                    $this->sendTo($player);
                  }
                ));
                break;
              case 1:
                $editor = new MinigameCreateForm(
                  function (?MinigameBlueprint $minigame) use ($player, $name) {
                    if (!$minigame) {
                      $this->sendTo($player);
                      return;
                    }
                    $mgStore = Plugin::getInstance()->getMinigameStore();
                    $mgStore->setMinigame($name, $minigame);
                    $this->notice .= "Updated Minigame: \"$name\"";
                    $this->sendTo($player);
                  },
                  $mg,
                  promptName: false
                );
                $editor->sendTo($player);
                break;
              case 2:
                $player->sendForm(new CustomForm(
                  "Minigame Copy's Name",
                  [
                    new Label("description", "Choose a name for the Minigame copy"),
                    new Input("name", "", "", $name),
                  ],
                  function (Player $player, CustomFormResponse $data) use ($name, $mg): void {
                    $copyName = $data->getString("name");
                    if (!MinigameStore::checkValidName($copyName) || $copyName === $name) {
                      $this->notice = "Copy canceled because the name \"$copyName\" was invalid.";
                      $this->sendTo($player);
                      return;
                    }
                    $mgStore = Plugin::getInstance()->getMinigameStore();
                    $mgStore->setMinigame($copyName, $mg);
                    $this->notice = "Copied \"$name\" to \"$copyName\"";
                    $this->sendTo($player);
                  },
                  function (Player $player): void {
                    $this->notice = "Copy Canceled";
                    $this->sendTo($player);
                  }
                ));
                break;
              case 3:
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
                    $this->notice = "Deleted minigame \"$name\". This action cannot be undone.";
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
