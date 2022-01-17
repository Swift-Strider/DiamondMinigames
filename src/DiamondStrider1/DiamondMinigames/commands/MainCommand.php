<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

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
      $sender->sendMessage("--- HELP:");
      $sender->sendMessage("You may make edits to the config files,");
      $sender->sendMessage("Or interactively edit them by running `/diamondminigames` as a player.");
      return;
    }
  }
}
