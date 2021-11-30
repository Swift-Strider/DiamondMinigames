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
    $minigames = Plugin::getInstance()->getMinigameStore()->getAll();
    $indexToName = [];
    foreach ($minigames as $mg) {
      $indexToName[] = $mg->name;
      $options[] = new MenuOption("§8" . $mg->name);
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
            function (?MinigameBlueprint $minigame) use ($player) {
              if (!$minigame) {
                $this->notice = "No Minigames were Created";
                $this->sendTo($player);
                return;
              }

              $mgStore = Plugin::getInstance()->getMinigameStore();
              if ($mgStore->get($minigame->name) !== null) {
                $oldName = $minigame->name;
                $minigame->name = "{$minigame->name}_" . random_int(1000, 9999);
                $this->notice = "A minigame by the name \"$oldName\" exists so \"{$minigame->name}\" was created instead";
              }

              $this->notice ??= "Created Minigame \"{$minigame->name}\"";
              $mgStore->set($minigame);
              $this->sendTo($player);
            }
          );
          $editor->sendTo($player);
          return;
        }

        $name = $indexToName[$selectedOption];
        if (Plugin::getInstance()->getMinigameStore()->get($name) === null) {
          $this->notice = "Minigame ($name) No Longer Exists";
          $this->sendTo($player);
          return;
        }

        $player->sendForm($this->createOptionForm($name));
      },
      function (Player $player): void {
        FormSessions::sendPrevious($player);
      }
    );
  }

  private function createOptionForm(string $name): Form
  {
    return new MenuForm(
      "Options for $name",
      "What do you want to do?",
      [
        new MenuOption("§2Edit"),
        new MenuOption("§2Copy"),
        new MenuOption("§cDelete"),
      ],
      function (Player $player, int $selectedOption) use ($name): void {
        $mg = Plugin::getInstance()->getMinigameStore()->get($name);
        if (!$mg) {
          $this->notice = "Minigame ($name) No Longer Exists";
          $this->sendTo($player);
          return;
        }
        switch ($selectedOption) {
          case 0:
            $editor = new MinigameCreateForm(
              function (?MinigameBlueprint $minigame) use ($player, $name) {
                if ($minigame === null) {
                  $this->sendTo($player);
                  return;
                }
                $mgStore = Plugin::getInstance()->getMinigameStore();
                if ($name !== $minigame->name) {
                  $mgStore->delete($name);
                  if ($mgStore->get($minigame->name) !== null) {
                    $oldName = $minigame->name;
                    $minigame->name = "{$minigame->name}_" . random_int(1000, 9999);
                    $this->notice = "A minigame by the name \"$oldName\" exists so \"{$minigame->name}\" was created instead";
                  }
                }
                $mgStore->set($minigame);
                $this->notice ??= "Updated Minigame: \"{$minigame->name}\"";
                $this->sendTo($player);
              },
              $mg
            );
            $editor->sendTo($player);
            break;
          case 1:
            (new MinigameCreateForm(
              function (?MinigameBlueprint $mg) use ($player, $name): void {
                if ($mg === null) {
                  $this->sendTo($player);
                  return;
                }
                $copyName = $mg->name;
                if ($copyName === $name) {
                  $this->notice = "Copy Canceled (use a unique name)";
                  $this->sendTo($player);
                  return;
                }
                $mgStore = Plugin::getInstance()->getMinigameStore();
                $mgStore->set($mg);
                $this->notice = "Copied \"$name\" to \"$copyName\"";
                $this->sendTo($player);
              },
              $mg
            ))->sendTo($player);
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
                $mgStore->delete($name);
                $this->notice = "Deleted minigame \"$name\". This action cannot be undone.";
                $this->sendTo($player);
              }
            ));
        }
      },
      function (Player $player): void {
        $this->sendTo($player);
      }
    );
  }
}
