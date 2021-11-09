<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\hooks;

use DiamondStrider1\DiamondMinigames\minigame\Team;
use pocketmine\Player;

class PlayerAddHook extends BaseHook
{
  private bool $isCanceled = false;
  private ?string $canceledMessage = null;

  public function __construct(
    private Player $player,
    private ?Team $team,
  ) {
  }

  public function getPlayer(): Player
  {
    return $this->player;
  }

  public function setTeam(Team $team): void
  {
    $this->team = $team;
  }

  public function getTeam(): ?Team
  {
    return $this->team;
  }

  public function setCanceled(bool $isCanceled, ?string $message = null): void {
    $this->isCanceled = $isCanceled;
    $this->canceledMessage = $message;
  }

  public function getCanceled(): bool
  {
    return $this->isCanceled;
  }

  public function getCanceledMessage(): ?string
  {
    return $this->canceledMessage;
  }
}
