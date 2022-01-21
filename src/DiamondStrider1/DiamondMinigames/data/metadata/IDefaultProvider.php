<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

/**
 * Provides a defaults array where config-key => value
 */
interface IDefaultProvider
{
    /**
     * @phpstan-return array<string, mixed>
     * config-key => value
     * 
     * The values this function returns must be
     * fully parsed.
     * 
     * Ex: The defaults in yaml
     * ```yaml
     * position: [0, 2, 4]
     * ```
     * would lead to `getDefaults()` returning
     * `["position" => new Vector3(0, 2, 4)]`
     */
    public static function getDefaults(): array;
}
