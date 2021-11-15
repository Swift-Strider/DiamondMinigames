<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\data\metadata\IntType;
use DiamondStrider1\DiamondMinigames\data\metadata\IValidationProvider;
use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerAddHook;
use DiamondStrider1\DiamondMinigames\minigame\Minigame;
use DiamondStrider1\DiamondMinigames\minigame\Team;
use DiamondStrider1\DiamondMinigames\misc\Result;

class PlayerFillQueued extends PlayerFillStrategy implements IValidationProvider
{
  #[IntType(
    config_key: "max-teams",
    description: "The highest number of teams per game"
  )]
  public int $maxTeams;

  #[IntType(
    config_key: "min-teams",
    description: "The lowest number of teams a game may start with"
  )]
  public int $minTeams;

  #[IntType(
    config_key: "max-members",
    description: "The highest number of members per team"
  )]
  public int $maxTeamMembers;

  #[IntType(
    config_key: "min-members",
    description: "The lowest number of members per team a game may start with"
  )]
  public int $minTeamMembers;

  public function isValid(): Result
  {
    $errors = [];
    if ($this->minTeams > $this->maxTeams) {
      $errors[] = "Min Teams MUST BE at most Max Teams";
    } elseif ($this->minTeams < 2) {
      $errors[] = "Min Teams MUST BE at least 2";
    }
    if ($this->minTeamMembers > $this->maxTeamMembers) {
      $errors[] = "Min Team Members MUST BE at most Max Team Members";
    } elseif ($this->minTeams < 1) {
      $errors[] = "Min Team Members MUST BE at least 1";
    }

    if (count($errors) > 0) {
      return Result::error(implode(", AND ", $errors));
    }
    return Result::ok();
  }

  public function createImpl(): IStrategyImpl
  {
    return new class($this) implements IStrategyImpl
    {
      private Minigame $minigame;
      public function __construct(
        private PlayerFillQueued $strategy
      ) {
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
        if ($sTeam === null && count($this->minigame->getTeams()) < $this->strategy->maxTeams)
          $sTeam = new Team("Team #" . (count($this->minigame->getTeams()) + 1));
        if ($sTeam !== null && count($sTeam->getPlayers()) < $this->strategy->maxTeamMembers)
          $hook->setTeam($sTeam);
      }
    };
  }
}
