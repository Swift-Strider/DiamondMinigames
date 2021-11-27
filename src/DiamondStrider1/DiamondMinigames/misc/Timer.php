<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\misc;

use Closure;
use DiamondStrider1\DiamondMinigames\Plugin;
use InvalidStateException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use TypeError;

/**
 * Class for running code periodically for a set amount of time
 * // TODO: Mainly used by PlayerQueuedFill's Impl to start the minigame
 */
class Timer
{
  private TaskHandler $task;

  public function __construct(
    private Closure $interval,
    private Closure $finished
  ) {
  }

  public function isRunning(): bool
  {
    return isset($this->task) && !$this->task->isCancelled();
  }

  public function start(int $ticks, int $totalTicks): void
  {
    if ($ticks > $totalTicks)
      throw new TypeError("\$ticks=$ticks is not less than \$totalTicks=$totalTicks");
    if ($this->isRunning())
      throw new InvalidStateException("Attempt to start a running timer");

    $tickCount = 0;
    $this->task = Plugin::getInstance()->getScheduler()->scheduleRepeatingTask(
      new ClosureTask(function (int $_currentTick) use (&$tickCount, $ticks, $totalTicks): void {
        $tickCount += $ticks;
        if ($tickCount < $totalTicks) {
          ($this->interval)();
        } else {
          ($this->finished)();
          $this->task->cancel();
        }
      }),
      $ticks
    );
  }

  public function stop(): void
  {
    if (!$this->isRunning())
      throw new InvalidStateException("Attempt to stop a non-running timer");

    $this->task->cancel();
  }
}
