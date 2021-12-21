<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\management;

use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use DiamondStrider1\DiamondMinigames\Plugin;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;

class WorldTemplateForm extends BaseForm
{
  private ?string $notice = null;

  protected function createForm(Player $player): Form
  {
    $options = [];
    $worlds = Plugin::getInstance()->getWorldTemplateManager()->getAll();
    $indexToName = [];
    foreach ($worlds as $template) {
      $indexToName[] = $template->getName();
      $options[] = new MenuOption("§8" . $template->getName());
    }
    $options[] = new MenuOption("§2Create A New Minigame World");
    $option_count = count($options);
    return new MenuForm(
      "Server Minigame Worlds",
      "Create, View, Edit, and Delete Minigame Worlds on the server" .
        ($this->notice !== null ? "\n§3" . $this->notice : ""),
      $options,
      function (Player $player, int $selectedOption) use ($option_count, $indexToName): void {
        $this->notice = null;
        if ($selectedOption === $option_count - 1) {
          $worlds = Server::getInstance()->getDataPath() . "worlds";
          $names = scandir($worlds);
          if ($names === false) throw new AssumptionFailedError("Can't access worlds folder");
          $names = array_values(array_filter($names, fn ($n) => $n !== "." && $n !== ".." && is_dir("$worlds/$n")));
          $editor = new CustomForm(
            "Configure World Template",
            [
              new Label("description", "All players in this world will be teleported away, so world data can be read"),
              new Input("name", "Template's Name"),
              new Dropdown("world", "World to Copy", $names)
            ],
            function (Player $player, CustomFormResponse $data) use ($names): void {
              $world = $names[$data->getInt("world")];
              $name = $data->getString("name");

              $wm = Server::getInstance()->getWorldManager();
              $w = $wm->getWorldByName($world);
              if ($w !== null && $wm->getDefaultWorld() === $w) {
                $this->notice = "The default world can't be made into a template, as it can't be unloaded. Consider changing the default world first.";
                $this->sendTo($player);
                return;
              }

              $wtm = Plugin::getInstance()->getWorldTemplateManager();
              if ($wtm->get($name) !== null) {
                $oldName = $name;
                $name = "{$name}_" . random_int(1000, 9999);
                $this->notice = "A World Template by the name \"$oldName\" exists so \"{$name}\" was created instead";
              }

              $this->notice ??= "Created World Template \"{$name}\"";
              Plugin::getInstance()->getWorldTemplateManager()->add($name, $world);
              $this->sendTo($player);
            },
            function (Player $player): void {
              $this->sendTo($player);
            }
          );
          $player->sendForm($editor);
          return;
        }

        $name = $indexToName[$selectedOption];
        if (Plugin::getInstance()->getWorldTemplateManager()->get($name) === null) {
          $this->notice = "Minigame World ($name) No Longer Exists";
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
        new MenuOption("§2Update"),
        new MenuOption("§2Copy to World"),
        new MenuOption("§cDelete"),
      ],
      function (Player $player, int $selectedOption) use ($name): void {
        $template = Plugin::getInstance()->getWorldTemplateManager()->get($name);
        if (!$template) {
          $this->notice = "Minigame World ($name) No Longer Exists";
          $this->sendTo($player);
          return;
        }
        switch ($selectedOption) {
          case 0:
            $this->notice = null;
            $worlds = Server::getInstance()->getDataPath() . "worlds";
            $names = scandir($worlds);
            if ($names === false) throw new AssumptionFailedError("Can't access worlds folder");
            $names = array_values(array_filter($names, fn ($n) => $n !== "." && $n !== ".." && is_dir("$worlds/$n")));
            $editor = new CustomForm(
              "Configure World Template",
              [
                new Label("description", "All players in this world will be teleported away, so world data can be read"),
                new Input("name", "Template's Name", "", $name),
                new Dropdown("world", "World to Copy", $names)
              ],
              function (Player $player, CustomFormResponse $data) use ($names, $name): void {
                $world = $names[$data->getInt("world")];
                $newName = $data->getString("name");

                $wm = Server::getInstance()->getWorldManager();
                $w = $wm->getWorldByName($world);
                if ($w !== null && $wm->getDefaultWorld() === $w) {
                  $this->notice = "The default world can't be made into a template, as it can't be unloaded. Consider changing the default world first.";
                  $this->sendTo($player);
                  return;
                }

                $wtm = Plugin::getInstance()->getWorldTemplateManager();
                $wtm->delete($name);
                if ($wtm->get($newName) !== null) {
                  $oldName = $newName;
                  $newName = "{$newName}_" . random_int(1000, 9999);
                  $this->notice = "A World Template by the name \"$oldName\" exists so \"{$newName}\" was created instead";
                }

                $this->notice ??= "Updated World Template with name \"{$newName}\"";
                Plugin::getInstance()->getWorldTemplateManager()->add($newName, $world);
                $this->sendTo($player);
              },
              function (Player $player): void {
                $this->sendTo($player);
              }
            );
            $player->sendForm($editor);
            break;
          case 1:
            $player->sendForm(new CustomForm(
              "World Name",
              [
                new Input("name", "The name of the new World")
              ],
              function (Player $player, CustomFormResponse $data) use($name): void {
                $template = Plugin::getInstance()->getWorldTemplateManager()->get($name);
                if ($template === null) {
                  $this->notice = "Minigame World ($name) No Longer Exists";
                  $this->sendTo($player);
                  return;
                }
                $template->create($data->getString("name"));
              }
            ));
            break;
          case 2:
            $player->sendForm(new ModalForm(
              "Delete the minigame world $name?",
              "The minigame world will be gone forever (A really long time)",
              function (Player $player, bool $choice) use ($name): void {
                if (!$choice) {
                  $this->sendTo($player);
                  return;
                }
                $wtm = Plugin::getInstance()->getWorldTemplateManager();
                $wtm->delete($name);
                $this->notice = "Deleted minigame world \"$name\". This action cannot be undone.";
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
