<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames;

use pocketmine\player\Player;
use pocketmine\Server;
use Ramsey\Uuid\UuidInterface;

class MinigamePlayer
{
    public function __construct(
        private UuidInterface $uuid,
        private string $displayName,
    ) {
    }

    public static function fromPlayer(Player $player): self
    {
        return new self($player->getUniqueId(), $player->getDisplayName());
    }

    public function getUUID(): UuidInterface
    {
        return $this->uuid;
    }

    public function getPlayer(): ?Player
    {
        return Server::getInstance()->getPlayerByUUID($this->uuid);
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }
}
