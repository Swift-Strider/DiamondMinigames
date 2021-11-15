<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data;

class ConfigContext
{
  private string $nestedKeys = "<root>";
  private int $depth = 0;

  public function __construct(
    private string $file
  ) {
  }

  public function addKey(string|int $key): self
  {
    if (is_int($key)) {
      $key = "[$key]";
    } else {
      $key = ".$key";
    }
    
    $context = new self($this->file);
    $context->nestedKeys = $this->nestedKeys . $key;
    $context->depth = $this->depth + 1;
    return $context;
  }

  public function getNestedKeys(): string
  {
    return $this->nestedKeys;
  }

  public function getDepth(): int
  {
    return $this->depth;
  }

  public function getFile(): string
  {
    return $this->file;
  }
}
