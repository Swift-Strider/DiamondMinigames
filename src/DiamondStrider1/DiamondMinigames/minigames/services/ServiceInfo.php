<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\services;

class ServiceInfo
{
    /**
     * @phpstan-param class-string $serviceClass
     * @phpstan-param array<class-string> $configDepends
     */
    public function __construct(
        private string $serviceClass,
        private array $configDepends,
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
     * @phpstan-return array<class-string>
     */
    public function getConfigDepends(): array
    {
        return $this->configDepends;
    }
}
