<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Attribute;
use Closure;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Toggle;
use pocketmine\form\Form;
use pocketmine\player\Player;

/**
 * @phpstan-implements IValueType<bool>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class BoolType implements IValueType
{
  public function __construct(
    private string $config_key,
    private string $description
  ) {
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
    return new CustomForm(
      "Toggle Boolean",
      [new Label("description", $this->description), new Toggle("boolean", "")],
      function (Player $player, CustomFormResponse $data) use ($callback): void {
        ($callback)($data->getBool("boolean"));
      },
      function (Player $player) use ($callback): void {
        ($callback)(null);
      }
    );
  }

  public function shortString(mixed $value): string
  {
    if (!is_bool($value)) return "NOT SET";
    return $value ? "ENABLED" : "DISABLED";
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    return $value ? "true" : "false";
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_bool($raw)) throw new ConfigException("Expected boolean", $context);
    return $raw;
  }
}
