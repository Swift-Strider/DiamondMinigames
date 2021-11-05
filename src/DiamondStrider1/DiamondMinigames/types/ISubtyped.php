<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\types;

/** Exposes API to find subtypes of an abstract IEditable */
interface ISubtyped
{
  /** @return class-string<IEditable&self>[] */
  public static function getSubtypes(): array;
}
