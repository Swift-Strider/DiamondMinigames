<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\configs;

trait ConfigurableTrait
{
    /**
     * @var array<string, BaseConfig> $configs
     */
    private array $configs = [];

    /**
     * @param array<BaseConfig> $configs
     */
    private function useConfigs(array $configs): void
    {
        foreach ($configs as $conf) {
            $this->configs[get_class($conf)] = $conf;
        }
    }

    /**
     * @phpstan-template T of BaseConfig
     * @phpstan-param class-string<T> $configClass
     * @phpstan-return T|null
     */
    private function requireConfig(string $configClass): ?object
    {
        /** @phpstan-ignore-next-line */
        return $this->configs[$configClass] ?? null;
    }
}
