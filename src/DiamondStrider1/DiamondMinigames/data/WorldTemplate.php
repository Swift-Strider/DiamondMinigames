<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\World;

class WorldTemplate
{
  public function __construct(
    private string $name,
    private WorldTemplateManager $wtm
  ) {
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function create(?string $name = null): World
  {
    if ($name === null) $name = "_temp_$this->name";
    $template = $this->wtm->getFolder() . "/$this->name";
    $dst = Server::getInstance()->getDataPath() . "worlds/$name";
    $wm = Server::getInstance()->getWorldManager();
    WorldTemplateManager::recursiveCopy($template, $dst);
    $wm->loadWorld($name);
    $world = $wm->getWorldByName($name);
    if ($world === null) throw new AssumptionFailedError("The world should exist");
    $world->setAutoSave(false);
    return $world;
  }
}
