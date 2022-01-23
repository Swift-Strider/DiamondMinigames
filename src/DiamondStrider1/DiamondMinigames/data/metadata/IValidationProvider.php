<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;

interface IValidationProvider
{
    /**
     * @throws ConfigException when validation fails
     */
    public function validate(ConfigContext $context): void;
}
