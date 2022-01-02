<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\MGPlayer;
use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\minigame\strategies\PlayerFillQueued;
use DiamondStrider1\DiamondMinigames\minigame\Team;
use DiamondStrider1\DiamondMinigames\misc\Result;
use DiamondStrider1\DiamondMinigames\misc\Timer;
use DiamondStrider1\DiamondMinigames\Plugin;
use pocketmine\entity\Location;
use pocketmine\world\World;

class PlayerFillQueuedImpl extends BasePlayerFillImpl
{
  private Minigame $minigame;
  private Timer $gameStart;
  private ?World $lobby = null;

  public function __construct(
    private PlayerFillQueued $strategy
  ) {
    $this->gameStart = new Timer(
      function (): void {
        $config = Plugin::getInstance()->getMainConfig();
        $config->gameStarting->sendMessage(
          [
            '$count' => (string) count($this->minigame->getPlayers()),
            '$min' => (string) ($this->strategy->minTeams * $this->strategy->minTeamMembers),
            '$max' => (string) ($this->strategy->maxTeams * $this->strategy->maxTeamMembers)
          ],
          $this->minigame->getPlayers()
        );
      },
      function (): void {
        $config = Plugin::getInstance()->getMainConfig();
        $config->gameStarted->sendMessage(
          [
            '$count' => (string) count($this->minigame->getPlayers()),
            '$min' => (string) ($this->strategy->minTeams * $this->strategy->minTeamMembers),
            '$max' => (string) ($this->strategy->maxTeams * $this->strategy->maxTeamMembers)
          ],
          $this->minigame->getPlayers()
        );
        $this->minigame->startGame();
      }
    );
  }

  public function onInit(Minigame $minigame): void
  {
    $this->minigame = $minigame;
    $teams = [];
    for ($i = 0; $i < $this->strategy->minTeams; $i++) {
      $teams[] = new Team("Team #" . ($i + 1));
    }
    $this->minigame->setTeams($teams);
  }

  public function onDestroy(): void
  {
  }

  public function addPlayer(MGPlayer $player): Result
  {
    if ($this->minigame->getState() !== Minigame::PENDING)
      return Result::error("This game is no longer accepting players");
    $sTeam = null;
    foreach ($this->minigame->getTeams() as $team) {
      if ($sTeam === null) $sTeam = $team;
      if (count($team->getPlayers()) < count($sTeam->getPlayers())) $sTeam = $team;
    }

    if ($sTeam !== null && count($sTeam->getPlayers()) === $this->strategy->maxTeamMembers)
      $sTeam = null;
    // Add another team if possible
    if ($sTeam === null && count($this->minigame->getTeams()) < $this->strategy->maxTeams) {
      $sTeam = new Team("Team #" . (count($this->minigame->getTeams()) + 1));
      $this->minigame->setTeams([...$this->minigame->getTeams(), $sTeam]);
    }
    // Could not find or add a team
    if ($sTeam === null) return Result::error("Could not find a suitable team");

    $this->lobby ??= $this->strategy->lobby->world->create();
    $s = $this->strategy->lobby->spawn;
    $player->getPlayer()->teleport(new Location($s->x, $s->y, $s->z, $this->lobby, 0, 0));
    $sTeam->addPlayer($player);

    $config = Plugin::getInstance()->getMainConfig();
    $config->playerJoined->sendMessage(
      [
        '$player' => $player->getPlayer()->getDisplayName(),
        '$count' => (string) (count($this->minigame->getPlayers()) + 1),
        '$min' => (string) ($this->strategy->minTeams * $this->strategy->minTeamMembers),
        '$max' => (string) ($this->strategy->maxTeams * $this->strategy->maxTeamMembers)
      ],
      [$player->getPlayer(), ...array_values($this->minigame->getPlayers())]
    );

    foreach ($this->minigame->getTeams() as $team) {
      if (count($team->getPlayers()) < $this->strategy->minTeamMembers) {
        return Result::ok();
      }
    }
    if (!$this->gameStart->isRunning())
      $this->gameStart->start(20, $this->strategy->waitTime * 20);
    return Result::ok();
  }

  public function removePlayer(MGPlayer $player): void
  {
    $config = Plugin::getInstance()->getMainConfig();
    $config->playerLeft->sendMessage(
      [
        '$player' => $player->getPlayer()->getDisplayName(),
        '$count' => (string) (count($this->minigame->getPlayers()) - 1),
        '$min' => (string) ($this->strategy->minTeams * $this->strategy->minTeamMembers),
        '$max' => (string) ($this->strategy->maxTeams * $this->strategy->maxTeamMembers)
      ],
      $this->minigame->getPlayers()
    );
    foreach ($this->minigame->getTeams() as $team)
      if ($team->hasPlayer($player)) $team->removePlayer($player);
    if ($this->gameStart->isRunning()) {
      foreach ($this->minigame->getTeams() as $team) {
        if (count($team->getPlayers()) < $this->strategy->minTeamMembers) {
          $this->gameStart->stop();
          return;
        }
      }
    }
  }
}
