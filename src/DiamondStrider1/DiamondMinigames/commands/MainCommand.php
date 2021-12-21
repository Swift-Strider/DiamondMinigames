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
    $this->onlyPlayers = true;
  }

  public function onRun(CommandSender $sender, string $commandLabel, array $args): void
  {
    if (!($sender instanceof Player))
      throw new AssertionError('$sender is guaranteed to be a player');
    (new ManageForm)->sendTo($sender);
  }
}
