<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\regions;

use DiamondStrider1\DiamondDatas\attributes\ListType;
use DiamondStrider1\DiamondDatas\attributes\ObjectType;
use DiamondStrider1\DiamondDatas\metadata\IDefaultProvider;

class RegionConfig implements IDefaultProvider
{
    /** @phpstan-var array<string, mixed> */
    private static array $defaults;

    /**
     * @var Region[]
     */
    #[ObjectType(Region::class)]
    #[ListType("regions", "All regions defined for this plugin")]
    public array $regions;

    public static function getDefaults(): array
    {
        if (!isset(self::$defaults)) {
            self::$defaults = [
                "regions" => []
            ];
        }

        return self::$defaults;
    }
}
