<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

use AssertionError;
use DiamondStrider1\DiamondMinigames\forms\management\ManageForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class MainCommand extends BaseCommand
{
  public function prepare(): void
  {
    $this->setPermission("diamondminigames.manage");
  }

  public function onRun(CommandSender $sender, string $commandLabel, array $args): void
  {
    if (!($sender instanceof Player)) {
      $mgs = $this->getOwningPlugin()->getMinigameStore()->getAll();
      $wts = $this->getOwningPlugin()->getWorldTemplateManager()->getAll();
      $sender->sendMessage(sprintf("Minigames (%d):\t%s", count($mgs), implode(", ", array_map(fn ($mg) => "\"$mg->name\"", $mgs))));
      $sender->sendMessage(sprintf("Minigames (%d):\t%s", count($wts), implode(", ", array_map(fn ($wt) => "\"{$wt->getName()}\"", $wts))));
      $sender->sendMessage("--- HELP:");
      $sender->sendMessage("You may make edits to the config files,");
      $sender->sendMessage("Or interactively edit them by running `/diamondminigames` as a player.");
      return;
    }
    (new ManageForm)->sendTo($sender);
  }
}
