<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\misc;

use DiamondStrider1\DiamondMinigames\data\metadata\IDefaultProvider;
use DiamondStrider1\DiamondMinigames\data\metadata\ObjectType;

class MainConfig implements IDefaultProvider
{
  /** @phpstan-var array<string, mixed> */
  private static array $defaults;

  #[ObjectType(MessageConfig::class, "player-joined", "Sent to players in queue when another player joins")]
  public MessageConfig $playerJoined;
  
  public static function getDefaults(): array
  {
    if (!isset(self::$defaults)) {
      self::$defaults = [
        "player-joined" => new MessageConfig('[JOIN] $player [$min/$max]', "chat"),
      ];
    }

    return self::$defaults;
  }
}
