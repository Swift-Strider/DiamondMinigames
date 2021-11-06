<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use Exception;

class ConfigException extends Exception
{
  const UNKNOWN_ERROR = -1;
  const TYPE_MISMATCH = 0;
  const FILE_IS_DIRECTORY = 1;

  /** @phpstan-param self::* $cause */
  public function __construct(
    private int $cause
  ) {
  }

  /** @phpstan-return self::* */
  public function getCause(): int
  {
    return $this->cause;
  }
}
