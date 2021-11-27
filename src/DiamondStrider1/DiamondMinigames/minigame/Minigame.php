<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use Closure;
use DiamondStrider1\DiamondMinigames\minigame\hooks\BaseHook;
use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerAddHook;
use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerRemoveHook;
use DiamondStrider1\DiamondMinigames\minigame\strategies\IStrategyImpl;
use DiamondStrider1\DiamondMinigames\misc\Result;
use pocketmine\Player;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class Minigame
{
  const PENDING = 0;
  const RUNNING = 1;
  const ENDED = 2;

  /** @phpstan-var self::* */
  private int $state;
  /** @var IStrategyImpl[] */
  private array $strategies = [];
  /** 
   * @var Closure[][]
   * @phpstan-var array<class-string<BaseHook>, array<Closure(BaseHook): void>>
   */
  private array $bindings = [];

  /** @var Team[] */
  private array $teams = [];
  /** @var array<string, Player> uuid => Player */
  private array $players = [];

  public function __construct(MinigameBlueprint $blueprint)
  {
    $this->state = self::PENDING;
    $this->strategies = $blueprint->buildStrategies();
    foreach ($this->strategies as $strategy) {
      $strategy->onInit($this);
      $rStrategy = new ReflectionClass($strategy);
      foreach ($rStrategy->getMethods(ReflectionMethod::IS_PUBLIC) as $rMethod) {
        if ($rMethod->isStatic() || count($rMethod->getParameters()) !== 1) continue;

        $type = $rMethod->getParameters()[0]->getType();
        if ($type instanceof ReflectionNamedType) {
          if (!class_exists($type->getName())) continue;
          $type = new ReflectionClass($type->getName());
          if (!$type->isSubclassOf(BaseHook::class)) continue;
          $closure = $rMethod->getClosure($strategy);
          if ($closure) $this->bindings[$type->getName()][] = $closure;
        }
      }
    }
  }

  /** @phpstan-return self::* */
  public function getState(): int
  {
    return $this->state;
  }

  public function addPlayer(Player $player): Result
  {
    if ($this->hasPlayer($player)) return Result::error("Already In Game");

    $hook = new PlayerAddHook($player, null);
    $this->processHook($hook);

    if ($hook->getCanceled()) return Result::error($hook->getCanceledMessage() ?? "Join Cancelled");
    if (!($team = $hook->getTeam())) return Result::error("No Empty Team Found");

    if (array_search($team, $this->teams, true) === false) $this->teams[] = $team;

    $this->players[$player->getRawUniqueId()] = $player;
    $team->addPlayer($player);
    return Result::ok();
  }

  public function hasPlayer(Player $player): bool
  {
    return array_search($player, $this->players, true) !== false;
  }

  /**
   * @return bool false if the player is not in the game
   */
  public function removePlayer(Player $player): bool
  {
    if (!isset($this->players[$player->getRawUniqueId()])) return false;
    $this->processHook(new PlayerRemoveHook($player));

    unset($this->players[$player->getRawUniqueId()]);
    foreach ($this->teams as $team)
      if ($team->hasPlayer($player)) $team->removePlayer($player);

    return true;
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

  public function processHook(BaseHook $hook): void
  {
    if (!isset($this->bindings[get_class($hook)])) return;
    foreach ($this->bindings[get_class($hook)] as $binding) {
      ($binding)($hook);
    }
  }
}
