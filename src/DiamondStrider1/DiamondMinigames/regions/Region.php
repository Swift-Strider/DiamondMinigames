<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\regions;

use DiamondStrider1\DiamondMinigames\data\attributes\StringType;
use DiamondStrider1\DiamondMinigames\data\attributes\VectorType;
use pocketmine\math\Vector3;

class Region
{
    #[StringType("id", "The UUID of this region")]
    public string $id;
    #[VectorType("start", "The start coords of this region")]
    public Vector3 $start;
    #[VectorType("end", "The end coords of this region")]
    public Vector3 $end;
    #[StringType("backupID", "The UUID of the backed-up world data for this region")]
    public string $backupID;

    public function __construct(string $id = null, Vector3 $start = null, Vector3 $end = null, string $backupID = null)
    {
        if ($id !== null) $this->id = $id;
        if ($start !== null) $this->start = $start;
        if ($end !== null) $this->end = $end;
        if ($backupID !== null) $this->backupID = $backupID;
    }
}
