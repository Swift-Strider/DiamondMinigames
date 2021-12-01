<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\commands;

use AssertionError;
use DiamondStrider1\DiamondMinigames\forms\player\PlayForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class PlayCommand extends BaseCommand
{
  public function prepare(): void
  {
    $this->setPermission("diamondminigames.play");
    $this->onlyPlayers = true;
  }

  public function onRun(CommandSender $sender, string $commandLabel, array $args): void
  {
    if (!($sender instanceof Player))
      throw new AssertionError('$sender is guaranteed to be a player');
    $mgManager = $this->getPlugin()->getMinigameManager();
    if ($mgManager->getPlaying($sender)) {
      $sender->sendMessage("§cYou are in a game! You must use /quit to leave, first!");
      return;
    }
    if (($name = $args[0] ?? null) !== null) {
      if ($mgManager->send($sender, $name)) {
        return;
      }
    }
    (new PlayForm)->sendTo($sender);
  }
}
