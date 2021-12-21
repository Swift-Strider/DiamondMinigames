<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\misc;

use DiamondStrider1\DiamondMinigames\data\metadata\IValidationProvider;
use DiamondStrider1\DiamondMinigames\data\metadata\StringType;
use pocketmine\player\Player;

class MessageConfig implements IValidationProvider
{
  public function __construct(
    // TODO: Have description point people to README.md for possible $-arguments
    #[StringType("message", '$arg will be replaced with it\'s value')]
    public string $message = "",
    #[StringType("display", "May be: (none, chat, actionbar)")]
    public string $display = "none"
  ) {
  }

  public function isValid(): Result
  {
    switch ($this->display) {
      case "none":
      case "chat":
      case "actionbar":
        return Result::ok();
      default:
        return Result::error(
          "The key `display` must be none, chat, or actionbar.\n" . "{$this->display} is not valid"
        );
    }
  }

  /**
   * @phpstan-param array<string, string> $args with leading `$`
   * @param Player[] $players
   */
  public function sendMessage(array $args, array $players): void
  {
    $message = str_replace(array_keys($args), array_values($args), $this->message);

    switch ($this->display) {
      case "chat":
        foreach ($players as $player) {
          $player->sendMessage($message);
        }
        break;
      case "actionbar";
        foreach ($players as $player) {
          $player->sendActionBarMessage($message);
        }
        break;
    }
  }
}
