<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\types;

/** Exposes API to find subtypes of an abstract IEditable */
interface ISubtyped
{
  /** 
   * @return array<string, class-string<IEditable&self>> An array indexed by human-readable strings
   */
  public static function getSubtypes(): array;
}
