<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\misc;

use Closure;
use DiamondStrider1\DiamondMinigames\Plugin;
use DomainException;
use InvalidStateException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use TypeError;

/**
 * Class for running code periodically for a set amount of time
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
        if ($totalTicks === 0) {
            ($this->finished)();
            return;
        }
        if ($ticks > $totalTicks)
            throw new TypeError("\$ticks=$ticks is not less than \$totalTicks=$totalTicks");
        if ($this->isRunning())
            throw new DomainException("Attempt to start a running timer");

        $tickCount = 0;
        $this->task = Plugin::getInstance()->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(function () use (&$tickCount, $ticks, $totalTicks): void {
                $tickCount += $ticks;
                if ($tickCount < $totalTicks) {
                    ($this->interval)($tickCount, $totalTicks);
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
            throw new DomainException("Attempt to stop a non-running timer");

        $this->task->cancel();
    }
}
