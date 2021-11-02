<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

use CortexPE\Commando\BaseCommand;
use DiamondStrider1\DiamondMinigames\forms\management\ManageForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class MainCommand extends BaseCommand
{
  protected function prepare(): void
  {
    $this->setPermission("diamondminigames.manage");
  }

  public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
  {
    if (!($sender instanceof Player)) {
      $this->sendUsage();
      return;
    }

    (new ManageForm)->sendTo($sender);
  }
}
