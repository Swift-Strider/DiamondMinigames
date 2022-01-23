<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\services;

class ServiceInfo
{
    /**
     * @phpstan-param class-string $serviceClass
     * @phpstan-param class-string $configClass
     */
    public function __construct(
        private string $serviceClass,
        private string $configClass,
    ) {
    }

    /**
     * @phpstan-return class-string
     */
    public function getServiceClass(): string
    {
        return $this->serviceClass;
    }

    /**
     * @phpstan-return class-string
     */
    public function getConfigClass(): string
    {
        return $this->configClass;
    }
}
