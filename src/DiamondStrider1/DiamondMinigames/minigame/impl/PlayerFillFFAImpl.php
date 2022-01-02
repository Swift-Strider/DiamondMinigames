<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\MGPlayer;
use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\minigame\strategies\PlayerFillFFA;
use DiamondStrider1\DiamondMinigames\minigame\Team;
use DiamondStrider1\DiamondMinigames\misc\Result;
use pocketmine\entity\Location;

class PlayerFillFFAImpl extends BasePlayerFillImpl
{
  private Minigame $minigame;

  public function __construct(
    private PlayerFillFFA $strategy
  ) {
  }

  public function onInit(Minigame $minigame): void
  {
    $this->minigame = $minigame;
  }

  public function onDestroy(): void
  {
  }

  public function addPlayer(MGPlayer $player): Result
  {
    $team = new Team("FFA TEAM #" . (count($this->minigame->getTeams()) + 1));
    $this->minigame->setTeams([...$this->minigame->getTeams(), $team]);
    if ($this->minigame->getState() === Minigame::PENDING) $this->minigame->startGame();
    /** @phpstan-ignore-next-line $max is guaranteed to be >= $min */
    $s = $this->strategy->spawns[random_int(0, count($this->strategy->spawns) - 1)];
    $player->getPlayer()->teleport(new Location($s->x, $s->y, $s->z, $this->minigame->getWorld(), 0, 0));
    $team->addPlayer($player);
    return Result::ok();
  }

  public function removePlayer(MGPlayer $player): void
  {
    $this->minigame->setTeams(array_filter(
      $this->minigame->getTeams(),
      function (Team $team) use ($player): bool {
        return !$team->hasPlayer($player);
      }
    ));
  }
}
