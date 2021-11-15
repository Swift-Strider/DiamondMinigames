<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use Exception;

class ConfigException extends Exception
{
  public function __construct(string $message, ?ConfigContext $context = null)
  {
    parent::__construct(self::getPrettyMessage($message, $context));
  }

  private static function getPrettyMessage(string $message, ?ConfigContext $context): string
  {
    $dashes = str_repeat("-", 15);
    $message = "$dashes ConfigException: $message $dashes";

    if ($context) {
      $headerLen = strlen($message);
      $file = $context->getFile();
      $prettyFile = "<pocketmine_server>" . substr($file, strrpos($file, "plugin_data", -1) - 1);
      $message .= "\n  Error in file \"$prettyFile\"\n";
      if ($context->getDepth() > 0) {
        $message .= "  This occurred at the key \"{$context->getNestedKeys()}\"\n";
      }
      $message .= str_repeat("-", $headerLen);
    }

    return $message;
  }
}
