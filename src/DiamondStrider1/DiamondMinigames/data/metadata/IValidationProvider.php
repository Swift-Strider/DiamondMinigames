<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use DiamondStrider1\DiamondMinigames\misc\Result;

interface IValidationProvider
{
  public function isValid(): Result;
}
