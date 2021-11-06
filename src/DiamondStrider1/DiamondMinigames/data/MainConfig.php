<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use DiamondStrider1\DiamondMinigames\types\IConfig;
use DiamondStrider1\DiamondMinigames\types\IEditable;

class MainConfig implements IEditable, IConfig
{
  /**
   * @config-key log-the-time
   * @type boolean
   */
  public bool $logTime;
  public static function getDefaults(): array
  {
    static $defaults = [
      "log-the-time" => false
    ];
    return $defaults;
  }
}
