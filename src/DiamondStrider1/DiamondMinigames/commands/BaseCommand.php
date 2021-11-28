<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

use DiamondStrider1\DiamondMinigames\Plugin;
use InvalidStateException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;

abstract class BaseCommand extends Command implements PluginIdentifiableCommand
{
  private ?CommandSender $sender;
  protected bool $onlyPlayers;

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

    $this->sender = $sender;
    $this->onRun($sender, $commandLabel, $args);
    $this->sender = null;
  }

  /** 
   * This is where perms are set and the command is configured
   */
  abstract public function prepare(): void;
  /** @param string[] $args */
  abstract public function onRun(CommandSender $sender, string $commandLabel, array $args): void;

  public function sendUsage(): void
  {
    $this->getSender()->sendMessage($this->getUsage());
  }

  public function getSender(): CommandSender
  {
    if (!isset($this->sender))
      throw new InvalidStateException("BaseCommand::getSender() called outside of onRun()");
    return $this->sender;
  }

  public function getPlugin(): Plugin
  {
    return Plugin::getInstance();
  }
}
