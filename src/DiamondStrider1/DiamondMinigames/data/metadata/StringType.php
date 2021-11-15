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
 * @phpstan-implements IValueType<string>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class StringType implements IValueType
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
      "Edit String",
      [new Label("description", $this->description), new Input("string", "")],
      function (Player $player, CustomFormResponse $data) use ($callback): void {
        ($callback)($data->getString("string"));
      },
      function (Player $player) use ($callback): void
      {
        ($callback)(null);
      }
    );
  }

  public function shortString(mixed $value): string
  {
    if (!is_string($value)) return "NOT SET";
    return "\"$value\"";
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    $value = str_replace(["\n", "\r"], ['\n', '\r'], (string) $value);
    return "\"$value\"";
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_string($raw)) throw new ConfigException("Expected string", $context);
    return $raw;
  }
}
