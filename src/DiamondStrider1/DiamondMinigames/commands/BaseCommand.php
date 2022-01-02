<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

use DiamondStrider1\DiamondMinigames\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;

abstract class BaseCommand extends Command implements PluginOwned
{
  protected bool $onlyPlayers = false;

  public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
  {
    parent::__construct($name, $description, $usageMessage, $aliases);
    $this->prepare();
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args)
  {
    if (!$this->testPermissionSilent($sender)) return;
    if ($this->onlyPlayers && !($sender instanceof Player)) {
      $sender->sendMessage("This command may only be used in-game");
      return;
    }

    $this->onRun($sender, $commandLabel, $args);
  }

  /**
   * This is where perms are set and the command is configured
   */
  abstract public function prepare(): void;
  /** @param string[] $args */
  abstract public function onRun(CommandSender $sender, string $commandLabel, array $args): void;

  public function getOwningPlugin(): Plugin
  {
    return Plugin::getInstance();
  }
}
