<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigame;

use DiamondStrider1\DiamondMinigames\data\metadata\VectorType;
use DiamondStrider1\DiamondMinigames\data\metadata\WorldTemplateType;
use DiamondStrider1\DiamondMinigames\data\WorldTemplate;
use pocketmine\math\Vector3;

class Lobby
{
  #[WorldTemplateType("world", "The world for the lobby")]
  public WorldTemplate $world;
  #[VectorType("spawn", "The spawn of the lobby")]
  public Vector3 $spawn;
}
