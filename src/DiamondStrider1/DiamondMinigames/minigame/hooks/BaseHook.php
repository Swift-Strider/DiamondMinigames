<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame\hooks;

use Closure;

abstract class BaseHook
{
  /** @phpstan-var Closure(static): void */
  private Closure $callback;

  public function callFinished(): void
  {
    if (isset($this->callback)) ($this->callback)($this);
  }

  /** @phpstan-param Closure(static): void $callback */
  public function onFinished(Closure $callback): void
  {
    if (!isset($this->callback)) $this->callback = $callback;
  }
}
