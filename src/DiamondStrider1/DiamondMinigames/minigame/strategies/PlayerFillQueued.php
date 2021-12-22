<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\strategies;

use DiamondStrider1\DiamondMinigames\data\metadata\IntType;
use DiamondStrider1\DiamondMinigames\data\metadata\IValidationProvider;
use DiamondStrider1\DiamondMinigames\data\metadata\ListType;
use DiamondStrider1\DiamondMinigames\data\metadata\ObjectType;
use DiamondStrider1\DiamondMinigames\data\metadata\VectorType;
use DiamondStrider1\DiamondMinigames\minigame\impl\PlayerFillQueuedImpl;
use DiamondStrider1\DiamondMinigames\minigame\Lobby;
use DiamondStrider1\DiamondMinigames\misc\Result;

class PlayerFillQueued extends PlayerFillStrategy implements IValidationProvider
{
  #[ObjectType(Lobby::class, "lobby", "The lobby used when players are in queue")]
  public Lobby $lobby;

  /** @var Vector3[] */
  #[ListType("team-spawns", "Separate Spawns for each team")]
  #[VectorType("", "Separate Spawns for each team")]
  public array $teamSpawns;

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

  #[IntType(
    config_key: "wait-time",
    description: "Period to wait before starting the game to allow for additional players"
  )]
  public int $waitTime;

  public function isValid(): Result
  {
    $errors = [];
    if (count($this->teamSpawns) !== $this->maxTeams) {
      $errors[] = "The number of Team Spawns must be equal to Max Teams";
    }
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

    if ($this->waitTime < 0) {
      $errors[] = "Wait Time MUST BE 0 or greater";
    }

    if (count($errors) > 0) {
      return Result::error(implode(", AND ", $errors));
    }
    return Result::ok();
  }

  public function createImpl(): PlayerFillQueuedImpl
  {
    return new PlayerFillQueuedImpl($this);
  }
}
