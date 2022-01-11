<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use DiamondStrider1\DiamondMinigames\minigame\events\MGPlayerAdded;
use DiamondStrider1\DiamondMinigames\minigame\events\MGPlayerRemoved;
use DiamondStrider1\DiamondMinigames\minigame\events\MinigameEnd;
use DiamondStrider1\DiamondMinigames\minigame\events\MinigameStart;
use DiamondStrider1\DiamondMinigames\minigame\impl\BasePlayerFillImpl;
use DiamondStrider1\DiamondMinigames\minigame\impl\IStrategyImpl;
use DiamondStrider1\DiamondMinigames\misc\Result;
use DiamondStrider1\DiamondMinigames\Plugin;
use pocketmine\player\Player;
use pocketmine\world\World;

class Minigame
{
  const PENDING = 0;
  const RUNNING = 1;
  const ENDED = 2;

  /** @phpstan-var self::* */
  private int $state;

  private BasePlayerFillImpl $playerFill;
  /** @var IStrategyImpl[] */
  private array $strategies = [];

  private ?World $world = null;
  /** @var Team[] */
  private array $teams = [];
  /** @var array<string, MGPlayer> uuid => MGPlayer */
  private array $players = [];

  public function __construct(
    private MinigameBlueprint $blueprint
  ) {
    $this->state = self::PENDING;
    [$this->playerFill, $this->strategies] = $blueprint->buildStrategies();
    $strategies = [$this->playerFill, ...$this->strategies];
    foreach ($strategies as $strategy) {
      $strategy->onInit($this);
    }
  }

  public function getBlueprint(): MinigameBlueprint
  {
    return $this->blueprint;
  }

  /** @phpstan-return self::* */
  public function getState(): int
  {
    return $this->state;
  }

  public function getWorld(): World
  {
    return $this->world ?? $this->world = $this->blueprint->world->create();
  }

  public function startGame(): void
  {
    if ($this->state !== self::PENDING) return;
    $this->state = self::RUNNING;
    $event = new MinigameStart($this);
    $event->call();
  }

  public function endGame(?Team $winningTeam): void
  {
    if ($this->state === self::PENDING) {
      foreach ($this->players as $player) {
        $player->getPlayer()->sendMessage("§cThe game you were in was forcibly closed, sorry. :(");
        $this->removePlayer($player);
      }
    } else if ($this->state === self::RUNNING) {
      $config = Plugin::getInstance()->getMainConfig();
      $config->gameEnded->sendMessage([], $this->getPlayers());
      if ($winningTeam) {

        $names = array_map(function (MGPlayer $v): string {
          return $v->getPlayer()->getDisplayName();
        }, $winningTeam->getPlayers());

        $config->gameWinners->sendMessage([
          '$winners' => implode(", ", $names)
        ], $this->getPlayers());

        foreach ($this->players as $player) {
          $this->removePlayer($player);
        }
      } else {
        $config->gameClosed->sendMessage([], $this->getPlayers());
      }
    }

    foreach ($this->strategies as $strategy) {
      $strategy->onDestroy();
    }

    $this->state = self::ENDED;
    $event = new MinigameEnd($this);
    $event->call();
  }

  public function addPlayer(MGPlayer $player): Result
  {
    if ($this->hasPlayer($player)) return Result::error("Already In Game");

    $result = $this->playerFill->addPlayer($player);
    if (!$result->success()) {
      return $result;
    }

    $this->players[$player->getID()] = $player;
    (new MGPlayerAdded($player))->call();

    return Result::ok();
  }

  public function hasPlayer(MGPlayer $player): bool
  {
    return in_array($player, $this->players, true);
  }

  /**
   * @return bool false if the player is not in the game
   */
  public function removePlayer(MGPlayer $player): bool
  {
    if (!isset($this->players[$player->getID()])) return false;
    $this->playerFill->removePlayer($player);
    (new MGPlayerRemoved($player))->call();
    unset($this->players[$player->getID()]);

    return true;
  }

  /** @return array<string, Player> uuid => Player */
  public function getPlayers(): array
  {
    return array_map(function (MGPlayer $v): Player {
      return $v->getPlayer();
    }, $this->players);
  }

  /** @return array<string, MGPlayer> uuid => MGPlayer */
  public function getMGPlayers(): array
  {
    return $this->players;
  }

  /** @return Team[] */
  public function getPlayingTeams(): array
  {
    $teams = [];
    foreach ($this->teams as $team) {
      if (!$team->isEliminated()) $teams[] = $team;
    }
    return $teams;
  }

  /** @return Team[] */
  public function getTeams(): array
  {
    return $this->teams;
  }

  /** @param Team[] $teams */
  public function setTeams(array $teams): void
  {
    $this->teams = $teams;
  }
}
