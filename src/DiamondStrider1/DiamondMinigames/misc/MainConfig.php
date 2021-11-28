<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\misc;

use DiamondStrider1\DiamondMinigames\data\metadata\IDefaultProvider;
use DiamondStrider1\DiamondMinigames\data\metadata\ObjectType;

class MainConfig implements IDefaultProvider
{
  /** @phpstan-var array<string, mixed> */
  private static array $defaults;

  ///        Begin MessageConfigs        \\\
  #[ObjectType(MessageConfig::class, "player-joined", "Sent to players in queue when another player joins (w/ PlayerFillQueued)")]
  public MessageConfig $playerJoined;
  #[ObjectType(MessageConfig::class, "player-left", "Sent to players in queue when another player leaves (w/ PlayerFillQueued)")]
  public MessageConfig $playerLeft;
  #[ObjectType(MessageConfig::class, "game-starting", "Repeatedly sent to players before a game starts (w/ PlayerFillQueued)")]
  public MessageConfig $gameStarting;
  #[ObjectType(MessageConfig::class, "game-started", "Sent to players once the game starts (w/ PlayerFillQueued)")]
  public MessageConfig $gameStarted;

  public static function getDefaults(): array
  {
    if (!isset(self::$defaults)) {
      self::$defaults = [
        "player-joined" => new MessageConfig('§2[JOIN]§g $player [$count/$min]', "chat"),
        "player-left" => new MessageConfig('§c[LEAVE]§g $player [$count/$min]', "chat"),
        "game-starting" => new MessageConfig('§6Game is starting in §c$time-left§8s', "actionbar"),
        "game-started" => new MessageConfig('§c[GAME] §6The game has begun!', "chat"),
      ];
    }

    return self::$defaults;
  }
}
