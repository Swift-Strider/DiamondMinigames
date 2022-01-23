<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\configs;

class ConfigManager
{
    /**
     * @var array<string, object> $configs
     */
    private array $configs = [];

    /**
     * @param array<object> $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $conf) {
            $this->configs[get_class($conf)] = $conf;
        }
    }

    /**
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $configClass
     * @phpstan-return T|null
     */
    public function get(string $configClass): ?object
    {
        /** @phpstan-ignore-next-line */
        return $this->configs[$configClass] ?? null;
    }
}
