<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames;

use DiamondStrider1\DiamondMinigames\minigames\configs\BaseConfig;
use DiamondStrider1\DiamondMinigames\minigames\configs\ConfigurableTrait;
use DiamondStrider1\DiamondMinigames\minigames\enums\PlayerAddResult;
use pocketmine\player\Player;

class Minigame
{
    use ConfigurableTrait;

    /**
     * @param array<BaseConfig> $configs
     */
    public function __construct(array $configs)
    {
        $this->useConfigs($configs);
    }

    #region === PLAYERS ===

    /** @var MinigamePlayer[] */
    private array $players = [];

    /** @phpstan-return PlayerAddResult::* */
    public function addPlayer(Player $player): int
    {
        return PlayerAddResult::UNSPECIFIED_ERROR;
    }

    public function quitPlayer(Player $player): void
    {
        return;
    }

    #endregion === PLAYERS ===

    #region === SPECTATORS ===

    /** @var array<string, Player> uuid => spectator */
    private array $spectators = [];

    public function addSpectator(Player $player): void
    {
        return;
    }

    public function removeSpectator(Player $player): void
    {
        return;
    }

    #endregion === SPECTATORS ===
}
