<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\configs;

use DiamondStrider1\DiamondDatas\metadata\ISubtypeProvider;
use DiamondStrider1\DiamondMinigames\minigames\services\MinigameServices;

abstract class BaseConfig implements ISubtypeProvider
{
    public static function getSubtypes(): array
    {
        static $subtypes = null;
        if ($subtypes === null) {
            $subtypes = [];
            foreach (MinigameServices::getAll() as $serviceInfo) {
                $name = $serviceInfo->getServiceClass();
                // Service has 6 characters `namespace\ChestService` => `Chest`
                $pretty = substr($name, strrpos($name, "\\") + 1, -6);
                $subtypes[$pretty] = $serviceInfo->getConfigClass();
            }
        }
        return $subtypes;
    }
}
