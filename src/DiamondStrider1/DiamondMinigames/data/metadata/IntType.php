<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Attribute;
use Closure;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use pocketmine\form\Form;
use pocketmine\Player;

/**
 * @phpstan-implements IValueType<int>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IntType implements IValueType
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
      "Edit Integer",
      [new Label("description", $this->description), new Input("int", "")],
      function (Player $player, CustomFormResponse $data) use ($callback): void {
        ($callback)((int) $data->getString("int"));
      },
      function (Player $player) use ($callback): void {
        ($callback)(null);
      }
    );
  }

  public function shortString(mixed $value): string
  {
    if (!is_int($value)) return "NOT SET";
    return "$value";
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    return (string) $value;
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_int($raw)) throw new ConfigException("Expected integer", $context);
    return $raw;
  }
}
