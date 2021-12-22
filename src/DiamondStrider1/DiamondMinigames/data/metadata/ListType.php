<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Attribute;
use Closure;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use pocketmine\form\Form;
use pocketmine\player\Player;
use TypeError;

/**
 * @phpstan-template T
 * @phpstan-implements IValueType<array<int, T>>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ListType implements IValueType
{
  /** @phpstan-var IValueType<T> */
  private IValueType $type;

  public function __construct(
    private string $config_key = "<root>",
    private string $description = ""
  ) {
  }

  public function setType(IValueType $type): void
  {
    $this->type = $type;
  }

  public function getKey(): string
  {
    return $this->config_key;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function createForm($value, Closure $callback): Form
  {
    if ($value === null) $value = [];
    $options[] = new MenuOption("Add New");
    $options[] = new MenuOption("Remove");
    foreach ($value as $i => $v) {
      $valueString = $this->type->shortString($v);
      $options[] = new MenuOption("$i => $valueString");
    }
    $options[] = new MenuOption("Submit");
    $option_count = count($options);
    return new MenuForm(
      "Configure Properties",
      "Choose an option to get started",
      $options,
      function (
        Player $player,
        int $selectedOption
      ) use ($callback, $value, $option_count): void {
        switch ($selectedOption) {
          case 0:
            $player->sendForm($this->type->createForm(
              null,
              function ($arrayValue) use ($value, $callback, $player): void {
                if ($arrayValue !== null) $value[] = $arrayValue;
                $player->sendForm($this->createForm($value, $callback));
              }
            ));
            break;
          case 1:
            $player->sendForm($this->createRemoveFrom($value, $callback));
            break;
          case $option_count - 1:
            /** @phpstan-var array<int, T> $value */
            ($callback)($value);
            break;
          default:
            $player->sendForm($this->type->createForm(
              $value[$selectedOption - 2],
              function ($arrayValue) use ($value, $callback, $player, $selectedOption): void {
                if ($arrayValue !== null) $value[$selectedOption - 2] = $arrayValue;
                $player->sendForm($this->createForm($value, $callback));
              }
            ));
            break;
        }
      },
      function (Player $player) use ($value, $callback): void {
        $player->sendForm(new ModalForm(
          "Abandon all data?",
          "Your changes will be lost!",
          function (Player $player, bool $choice) use ($value, $callback): void {
            if ($choice) ($callback)(null);
            else $player->sendForm($this->createForm($value, $callback));
          }
        ));
      }
    );
  }

  /**
   * @phpstan-param array<int, T> $value
   * @phpstan-param Closure(array<int, T>|null): void $callback
   */
  public function createRemoveFrom($value, Closure $callback): Form
  {
    foreach ($value as $i => $v) {
      $valueString = $this->type->shortString($v);
      $options[] = new MenuOption("§c$i => $valueString");
    }
    $options[] = new MenuOption("Done Removing!");
    $option_count = count($options);
    return new MenuForm(
      "Remove Properties",
      "Which would you like to remove",
      $options,
      function (
        Player $player,
        int $selectedOption
      ) use ($value, $callback, $option_count): void {
        if ($selectedOption === $option_count - 1) {
          $player->sendForm($this->createForm($value, $callback));
          return;
        }
        unset($value[$selectedOption]);
        $value = array_values($value);
        $player->sendForm($this->createRemoveFrom($value, $callback));
      },
      function (Player $player) use ($value, $callback): void {
        $player->sendForm($this->createForm($value, $callback));
      }
    );
  }

  public function shortString(mixed $value): string
  {
    if (!is_array($value)) return "NOT SET";
    return "List [...]";
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    if (!(is_array($value) && array_values($value) === $value))
      throw new TypeError("\$value must be an array-list");
    $lines = "\n";
    foreach ($value as $i => $v) {
      $newContext = $context->addKey($i);
      $valueLines = rtrim($this->type->yamlLines($v, $newContext));
      $padding = str_repeat("  ", $context->getDepth());
      $lines .= "$padding - $valueLines\n";
    }
    if ($lines === "\n") return "[]";
    return $lines;
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_array($raw)) throw new ConfigException("Expected key pair values", $context);
    /** @phpstan-var array<int, T> */
    $array = [];
    foreach ($raw as $i => $value) {
      $array[] = $this->type->fromRaw($value, $context->addKey($i));
    }
    return $array;
  }
}
