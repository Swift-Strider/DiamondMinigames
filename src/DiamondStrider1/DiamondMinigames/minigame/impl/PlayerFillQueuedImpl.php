<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\impl;

use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerAddHook;
use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerRemoveHook;
use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\minigame\strategies\PlayerFillQueued;
use DiamondStrider1\DiamondMinigames\minigame\Team;
use DiamondStrider1\DiamondMinigames\misc\Timer;
use DiamondStrider1\DiamondMinigames\Plugin;

class PlayerFillQueuedImpl implements IStrategyImpl
{
  private Minigame $minigame;
  private Timer $gameStart;

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

  public function onPlayerAdd(PlayerAddHook $hook): void
  {
    $sTeam = null;
    foreach ($this->minigame->getTeams() as $team) {
      if ($sTeam === null) $sTeam = $team;
      if (count($team->getPlayers()) < count($sTeam->getPlayers())) $sTeam = $team;
    }

    if ($sTeam !== null && count($sTeam->getPlayers()) === $this->strategy->maxTeamMembers)
      $sTeam = null;
    // Add another team if possible
    if ($sTeam === null && count($this->minigame->getTeams()) < $this->strategy->maxTeams)
      $sTeam = new Team("Team #" . (count($this->minigame->getTeams()) + 1));
    // Could not find or add a team
    if ($sTeam === null) return;

    $hook->setTeam($sTeam);
    $config = Plugin::getInstance()->getMainConfig();
    $config->playerJoined->sendMessage(
      [
        '$player' => $hook->getPlayer()->getDisplayName(),
        '$count' => (string) count($this->minigame->getPlayers()),
        '$min' => (string) ($this->strategy->minTeams * $this->strategy->minTeamMembers),
        '$max' => (string) ($this->strategy->maxTeams * $this->strategy->maxTeamMembers)
      ],
      $this->minigame->getPlayers()
    );

    foreach ($this->minigame->getTeams() as $team) {
      if (count($team->getPlayers()) < $this->strategy->minTeamMembers) {
        return;
      }
    }
    if (!$this->gameStart->isRunning())
      $this->gameStart->start(20, $this->strategy->waitTime * 20);
  }

  public function onPlayerRemove(PlayerRemoveHook $hook): void
  {
    $config = Plugin::getInstance()->getMainConfig();
    $config->playerLeft->sendMessage(
      [
        '$player' => $hook->getPlayer()->getDisplayName(),
        '$count' => (string) count($this->minigame->getPlayers()),
        '$min' => (string) ($this->strategy->minTeams * $this->strategy->minTeamMembers),
        '$max' => (string) ($this->strategy->maxTeams * $this->strategy->maxTeamMembers)
      ],
      $this->minigame->getPlayers()
    );
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