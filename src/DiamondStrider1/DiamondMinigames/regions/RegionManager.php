<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\regions;

use DiamondStrider1\DiamondMinigames\data\FileStore;
use DiamondStrider1\DiamondMinigames\data\NeoConfig;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\World;
use Ramsey\Uuid\Uuid;

class RegionManager
{
    /** @param NeoConfig<RegionConfig> $regionConfig */
    public function __construct(
        private NeoConfig $regionConfig,
        private FileStore $worldBackups,
    ) {
    }

    /**
     * @return Region[]
     */
    public function getAll(bool $reload = false): array
    {
        if ($reload) {
            $this->worldBackups->getAll();
        }

        /** @var RegionConfig $regionConfig */
        $regionConfig = $this->regionConfig->getObject($reload);

        return $regionConfig->regions;
    }

    /**
     * Creates a backup of an **unloaded** world and saves a region of it
     */
    public function saveRegion(string $worldFolderName, Vector3 $start, Vector3 $end): Region {
        $id = Uuid::uuid4()->getHex()->toString();
        $backupID = Uuid::uuid4()->getHex()->toString();
        $region = new Region($id, $start, $end, $backupID);

        $regionConfig = $this->regionConfig->getObject();
        $regionConfig->regions[] = $region;
        $this->regionConfig->setObject($regionConfig);

        $worldFile = Server::getInstance()->getDataPath() . 'worlds/' . $worldFolderName;
        $this->worldBackups->saveFile($worldFile, $backupID);
        return $region;
    }

    public function removeRegion(Region $region): void
    {
        /** @var RegionConfig $regionConfig */
        $regionConfig = $this->regionConfig->getObject();
        $regionConfig->regions = array_filter($regionConfig->regions, function (Region $region) {
            return $region->id !== $region->id;
        });
        $this->regionConfig->setObject($regionConfig);
        $this->worldBackups->remove($region->backupID);
    }
}
