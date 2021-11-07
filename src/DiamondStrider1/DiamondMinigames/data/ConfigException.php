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
    string $message,
    private int $cause = -1,
  ) {
    parent::__construct($message);
  }

  /** @phpstan-return self::* */
  public function getCause(): int
  {
    return $this->cause;
  }

  public static function unknownError(string $message): ConfigException
  {
    return new self($message, ConfigException::UNKNOWN_ERROR);
  }

  public static function typeMismatch(string $message): ConfigException
  {
    return new self($message, ConfigException::TYPE_MISMATCH);
  }

  public static function fileIsDirectory(string $message): ConfigException
  {
    return new self($message, ConfigException::FILE_IS_DIRECTORY);
  }
}
