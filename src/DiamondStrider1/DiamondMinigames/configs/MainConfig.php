<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\configs;

use DiamondStrider1\DiamondMinigames\data\metadata\IDefaultProvider;
use DiamondStrider1\DiamondMinigames\data\attributes\ObjectType;

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
    #[ObjectType(MessageConfig::class, "game-ended", "Sent to players once the game ends or closes (w/ PlayerFillQueued)")]
    public MessageConfig $gameEnded;
    #[ObjectType(MessageConfig::class, "game-winners", "Sent to players to announce winners (w/ PlayerFillQueued)")]
    public MessageConfig $gameWinners;
    #[ObjectType(MessageConfig::class, "game-closed", "Sent to players when the game was forcibly closed (w/ PlayerFillQueued)")]
    public MessageConfig $gameClosed;

    public static function getDefaults(): array
    {
        if (!isset(self::$defaults)) {
            self::$defaults = [
                "player-joined" => new MessageConfig('§2[JOIN]§g $player [$count/$min]', "chat"),
                "player-left" => new MessageConfig('§c[LEAVE]§g $player [$count/$min]', "chat"),
                "game-starting" => new MessageConfig('§6Game is starting in §c$time-left§8s', "actionbar"),
                "game-started" => new MessageConfig('§c[GAME] §6The game has begun!', "chat"),
                "game-ended" => new MessageConfig('§c[GAME] §6The game has finished!', "chat"),
                "game-winners" => new MessageConfig('§c[GAME] §6The winners are $winners!\n', "chat"),
                "game-closed" => new MessageConfig('§c[GAME] §6This game was manually closed, sorry.\n', "chat"),
            ];
        }

        return self::$defaults;
    }
}
