<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

use CortexPE\Commando\BaseCommand;
use DiamondStrider1\DiamondMinigames\Plugin;
use pocketmine\command\CommandSender;

class MainCommand extends BaseCommand
{
  protected function prepare(): void
  {
    $this->setPermission("diamondminigames.manage");
  }

  public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
  {
  }
}
