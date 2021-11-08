<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\types;

/** Exposes API to validate user input */
interface IValid
{
  public function isValid(): Result;
}
