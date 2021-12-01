<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use AssertionError;
use Closure;
use DiamondStrider1\DiamondMinigames\minigame\hooks\BaseHook;
use DiamondStrider1\DiamondMinigames\minigame\hooks\MinigameEndHook;
use DiamondStrider1\DiamondMinigames\minigame\hooks\MinigameStartHook;
use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerAddHook;
use DiamondStrider1\DiamondMinigames\minigame\hooks\PlayerRemoveHook;
use DiamondStrider1\DiamondMinigames\minigame\impl\BasePlayerFillImpl;
use DiamondStrider1\DiamondMinigames\minigame\impl\IStrategyImpl;
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

  private BasePlayerFillImpl $playerFill;
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
    [$this->playerFill, $this->strategies] = $blueprint->buildStrategies();
    $strategies = [$this->playerFill, ...$this->strategies];
    foreach ($strategies as $strategy) {
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

  public function startGame(): void
  {
    if ($this->state !== self::PENDING) return;
    $hook = new MinigameStartHook;
    $this->processHook($hook, function (): void {
      $this->state = self::RUNNING;
    });
  }

  public function endGame(?Team $winningTeam): void
  {
    if ($this->state !== self::RUNNING) return;
    $hook = new MinigameEndHook($winningTeam);
    $this->processHook($hook, function (): void {
      $this->state = self::ENDED;
    });
  }

  public function addPlayer(Player $player): Result
  {
    if ($this->hasPlayer($player)) return Result::error("Already In Game");

    [$result, $team] = $this->playerFill->addPlayer($player);
    if (!$result->success()) {
      return $result;
    }
    if ($team === null)
      throw new AssertionError(get_class($this->playerFill) . 'did not provide a $team');

    $this->players[$player->getRawUniqueId()] = $player;
    $hook = new PlayerAddHook($player, $team);
    $this->processHook($hook);

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
    $this->playerFill->removePlayer($player);
    $this->processHook(new PlayerRemoveHook($player));
    unset($this->players[$player->getRawUniqueId()]);

    return true;
  }

  /** @return array<string, Player> uuid => Player */
  public function getPlayers(): array
  {
    return $this->players;
  }

  /** @return Team[] */
  public function getPlayingTeams(): array
  {
    $teams = [];
    foreach ($this->teams as $team) {
      if (count($team->getPlayers()) > 0) $teams[] = $team;
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

  /** @phpstan-var array<array{BaseHook, null|Closure(): void}> */
  private array $hookQueue = [];
  private bool $isProcessingHook = false;
  /** @phpstan-param null|Closure(): void $cb */
  public function processHook(BaseHook $hook, ?Closure $cb = null): void
  {
    if (!isset($this->bindings[get_class($hook)])) return;
    $this->hookQueue[] = [$hook, $cb];
    // processHook was called while processing a different hook
    if ($this->isProcessingHook) return;

    $this->isProcessingHook = true;
    while ($value = array_shift($this->hookQueue)) {
      [$hook, $cb] = $value;
      foreach ($this->bindings[get_class($hook)] as $binding) {
        ($binding)($hook);
      }
      if ($cb) ($cb)();
    }
    $this->isProcessingHook = false;
  }
}
