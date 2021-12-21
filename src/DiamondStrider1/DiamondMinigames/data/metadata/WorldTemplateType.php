<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\data\metadata;

use Attribute;
use Closure;
use DiamondStrider1\DiamondMinigames\data\ConfigContext;
use DiamondStrider1\DiamondMinigames\data\ConfigException;
use DiamondStrider1\DiamondMinigames\data\WorldTemplate;
use DiamondStrider1\DiamondMinigames\forms\management\WorldTemplateForm;
use DiamondStrider1\DiamondMinigames\Plugin;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Label;
use pocketmine\form\Form;
use pocketmine\player\Player;
use TypeError;

/**
 * @phpstan-implements IValueType<WorldTemplate>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class WorldTemplateType implements IValueType
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

  public function createForm($value, Closure $callback, ?string $lastError = null): Form
  {
    $wtm = Plugin::getInstance()->getWorldTemplateManager();
    $worlds = array_values($wtm->getAll());
    return new CustomForm(
      "Change Minigame World",
      [
        new Label("description", $this->description . "\n" . ($lastError ?? "")),
        new Dropdown("world", "Choose a Minigame World", [...array_map(fn ($w) => $w->getName(), $worlds), "Create New"])
      ],
      function (Player $player, CustomFormResponse $data) use ($callback, $worlds): void {
        $world = $worlds[$data->getInt("world")] ?? null;
        if ($world === null) {
          $editor = new WorldTemplateForm(function (?WorldTemplate $template) use ($callback, $player): void {
            if ($template !== null) ($callback)($template);
            else
              $player->sendForm($this->createForm(null, $callback));
          });
          $editor->sendTo($player);
          return;
        }
        ($callback)($world);
      },
      function (Player $player) use ($callback): void {
        ($callback)(null);
      }
    );
  }

  public function shortString(mixed $value): string
  {
    if (!($value instanceof WorldTemplate)) return "NOT SET";
    return "\"{$value->getName()}\"";
  }

  public function yamlLines(mixed $value, ConfigContext $context): string
  {
    if (!($value instanceof WorldTemplate)) throw new TypeError("\$value must be a World");
    return "\"{$value->getName()}\"";
  }

  public function fromRaw(mixed $raw, ConfigContext $context): mixed
  {
    if (!is_string($raw)) throw new ConfigException("Expected string", $context);
    $template = Plugin::getInstance()->getWorldTemplateManager()->get($raw);
    if ($template === null) throw new ConfigException("The world \"$raw\" no longer exists", $context);
    return $template;
  }
}
