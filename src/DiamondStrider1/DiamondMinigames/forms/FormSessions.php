<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms;

use DiamondStrider1\DiamondMinigames\Plugin;
use InvalidStateException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class FormSessions
{
  /** @var FormSessions[] playerRawUUID => FormSession */
  private static array $playerSessions = [];

  #
  # Event Handlers
  #

  public static function playerAdded(Player $player)
  {
    self::$playerSessions[$player->getRawUniqueId()] = new FormSessions;
  }

  public static function playerRemoved(Player $player)
  {
    unset(self::$playerSessions[$player->getRawUniqueId()]);
  }

  public static function registerHandlers()
  {
    $plugin = Plugin::getInstance();
    $plugin->getServer()->getPluginManager()->registerEvents(
      new class implements Listener
      {
        public function onPlayerAdded(PlayerJoinEvent $ev)
        {
          FormSessions::playerAdded($ev->getPlayer());
        }

        public function onPlayerRemoved(PlayerQuitEvent $ev)
        {
          FormSessions::playerRemoved($ev->getPlayer());
        }
      },
      $plugin
    );
  }

  #
  # Session Functions
  #

  public static function pushPrevious(Player $player, BaseForm $form): void
  {
    self::$playerSessions[$player->getRawUniqueId()]->pushForm($form);
  }

  public static function sendPrevious(Player $player): void
  {
    $form = self::$playerSessions[$player->getRawUniqueId()]->popForm();
    if ($form) {
      $form->sendTo($player);
    }
  }

  /** @var BaseForm[] */
  private $forms = [];
  private function __construct()
  {
  }

  private function pushForm(BaseForm $form): void
  {
    array_push($this->forms, $form);
  }

  private function popForm(): BaseForm
  {
    return array_pop($this->forms);
  }
}
