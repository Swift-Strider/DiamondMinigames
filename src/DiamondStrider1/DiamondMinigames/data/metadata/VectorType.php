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
use pocketmine\math\Vector3;
use pocketmine\Player;
use TypeError;

/**
 * @phpstan-implements IValueType<Vector3>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class VectorType implements IValueType
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
      [new Label("description", $this->description), new Input("vector", "")],
      function (Player $player, CustomFormResponse $data) use ($callback): void {
        $matches = [];
        preg_match(
          '/(-?\d+(?:\.\d*)?) *, *(-?\d+(?:\.\d*)?) *, *(-?\d+(?:\.\d*)?)/',
          $data->getString("vector"),
          $matches
        );
        $rawVector = array_slice($matches, 1);
        if (count($rawVector) < 3) {
          ($callback)(null);
          return;
        }
        ($callback)(new Vector3(
          (float) $rawVector[0],
          (float) $rawVector[1],
          (float) $rawVector[2]
        ));
      },
      function (Player $player) use ($callback): void {
        ($callback)(null);
      }
    );
  }

  public function shortString(mixed $value): string
  {
    if (!($value instanceof Vector3)) return "NOT SET";
    return sprintf("(x: %.2f, y: %.2f, z: %.2f)", $value->x, $value->y, $value->z);
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    if (!($value instanceof Vector3))
      throw new TypeError("\$value is not a Vector3");
    return sprintf("[%.6f, %.6f, %.6f]", $value->x, $value->y, $value->z);
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_array($raw) || count($raw) < 3)
      throw new ConfigException("Expected Vector3 (list of 3 numbers)", $context);
    return new Vector3((float) $raw[0], (float) $raw[1], (float) $raw[2]);
  }
}
