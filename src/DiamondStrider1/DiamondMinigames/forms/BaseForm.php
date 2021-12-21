<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;

abstract class BaseForm
{
  abstract protected function createForm(Player $player): Form;

  public function sendTo(Player $player): void
  {
    $player->sendForm($this->createForm($player));
  }

  protected function openForm(Player $player, BaseForm $form): void
  {
    FormSessions::pushPrevious($player, $this);
    $form->sendTo($player);
  }
}
