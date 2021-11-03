<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use DiamondStrider1\DiamondMinigames\types\IEditable;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use pocketmine\form\Form;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use TypeError;

class ObjectForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"               => null,
    "description"         => null,
    "class"               => "",
  ];

  private bool $error = false;
  private array $formResult = [];
  /** @var mixed[][]
   * $propName => ["type" => "", "annotations" => []]
   */
  private array $types = [];

  public function __construct(array $annotations = [], IEditable $default = null)
  {
    parent::__construct($annotations, $default);
    $this->load($default);
  }

  private function load(?IEditable $default)
  {
    $className = $this->getAnnotation("class");
    try {
      $rClass = new ReflectionClass($className);
    } catch (ReflectionException) {
      throw new TypeError("Class does not exist: $className");
    }
    if (!$rClass->implementsInterface(IEditable::class)) {
      throw new TypeError("Class is not IEditable: $className");
    }
    $constructorParams = $rClass->getConstructor()?->getParameters();
    if ($constructorParams && count($constructorParams) > 0) {
      throw new TypeError("Class has a constructor with at least one parameter: $className");
    }

    foreach ($rClass->getProperties(ReflectionProperty::IS_PUBLIC) as $rProp) {
      if ($rProp->isStatic()) continue;

      preg_match_all(
        '/@(\S+) ?(.*)\n?/',
        str_replace("\r\n", "\n", $rProp->getDocComment()),
        $matches
      );
      if (count($matches) < 3) continue;

      $annotations = array_combine($matches[1], $matches[2]);

      if (!$annotations || !isset($annotations["type"])) {
        continue;
      }
      $type = $annotations["type"];
      unset($annotations["type"]);

      $this->types[$rProp->getName()] = [
        "type" => $type,
        "annotations" => $annotations
      ];

      if ($default) {
        $this->formResult[$rProp->getName()] = $rProp->getValue($default);
      } else {
        $this->formResult[$rProp->getName()] = null;
      }
    }
  }

  protected function createForm(Player $player): Form
  {
    $indexToPropName = [];
    $options = [];
    foreach ($this->formResult as $prop => $value) {
      $indexToPropName[] = $prop;
      $typeName = ucfirst($this->types[$prop]["type"]);
      if ($value !== null) {
        switch ($this->types[$prop]["type"]) {
          case "string":
          case "boolean":
          case "integer":
          case "float":
            $valueString = (string) $value;
            break;
          case "vector":
            /** @var Vector3 $value */
            [$x, $y, $z] = [$value->getX(), $value->getY(), $value->getZ()];
            $valueString = "Vector($x, $y, $z)";
            break;
          case "list":
            $valueString = "List [...]";
            break;
          case "object":
            $objectClass = $this->types[$prop]["annotations"]["class"];
            $objectName = substr($objectClass, strrpos($objectClass, "\\", -1) + 1);
            $valueString = "$objectName {...}";
            break;
          default:
            $valueString = $typeName;
        }
      } else {
        $valueString = "§c(UNFILLED)";
      }
      $options[] = new MenuOption("§c" . ucfirst($prop) . "§r is $valueString");
    }

    $options[] = new MenuOption("Submit");
    $option_count = count($options);
    $className = substr(
      $this->getAnnotation("class"),
      strrpos($this->getAnnotation("class"), "\\", -1) + 1
    );

    $form = new MenuForm(
      $this->getAnnotation("label") ?? $className,
      ($this->getAnnotation("description") ?? "Edit the $className") .
        ($this->error ? "\n§cYou haven't filled every property!" : ""),
      $options,
      function (Player $player, int $selectedOption) use ($indexToPropName, $option_count): void {
        $prop = $indexToPropName[$selectedOption] ?? null;
        switch ($selectedOption) {
          case $option_count - 1:
            $this->tryFinish($player);
            break;
          default:
            $editor = EditForm::build(
              $this->getPropType($prop),
              $this->getPropAnnotations($prop),
              $this->formResult[$prop]
            );
            $editor->onFinish(function ($value) use ($prop): void {
              if ($value === null) return;
              $this->error = false;
              $this->formResult[$prop] = $value;
            });
            $this->openForm($player, $editor);
        }
      },
      function (Player $player): void {
        $player->sendForm(new ModalForm(
          "Exiting?",
          "Are you sure you want to loose your progress.",
          function (Player $player, bool $choice): void {
            if ($choice) {
              $this->setFinished(null, $player);
            } else {
              $this->sendTo($player);
            }
          }
        ));
      }
    );

    return $form;
  }

  private function getPropType(string $propName): string
  {
    return $this->types[$propName]["type"];
  }

  private function getPropAnnotations(string $propName): array
  {
    return $this->types[$propName]["annotations"];
  }

  private function tryFinish(Player $player): void
  {
    $class = $this->getAnnotation("class");
    $object = new $class;
    foreach (array_keys($this->types) as $prop) {
      if ($this->formResult[$prop] === null) {
        $this->error = true;
        $this->sendTo($player);
        return;
      }
      $object->{$prop} = $this->formResult[$prop];
    }
    $this->setFinished($object, $player);
  }

  protected function getDefaultAnnotations(): array
  {
    return self::DEFAULT_ANNOTATIONS;
  }
}
