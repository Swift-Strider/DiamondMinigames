<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use DiamondStrider1\DiamondMinigames\types\IEditable;
use DiamondStrider1\DiamondMinigames\types\ISubtyped;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use pocketmine\form\Form;
use pocketmine\Player;
use ReflectionClass;
use ReflectionProperty;
use TypeError;

/**
 * @template T of IEditable
 * @extends EditForm<T|null>
 */
class ObjectForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"               => null,
    "description"         => null,
    "class"               => "",
  ];

  private bool $error = false;
  /** @var array<string, mixed> */
  private array $formResult = [];
  /** @phpstan-var class-string<IEditable&ISubtyped>[] */
  private array $subtypes;
  /**
   * @var array<string, array{type: string, annotations: string[], prop_ref: ReflectionProperty}>
   */
  private array $types = [];

  /**
   * @param string[] $annotations
   * @phpstan-param array{class: class-string<T>}&array<string, string> $annotations
   * @phpstan-param T $default
   */
  public function __construct(array $annotations, IEditable $default = null)
  {
    parent::__construct($annotations, $default);
    $this->load();
  }

  private function load(): void
  {
    $default = $this->getDefault();
    $className = $this->getAnnotationNonNull("class");

    if (!class_exists($className)) throw new TypeError("Class does not exist: $className");
    $rClass = new ReflectionClass($className);

    if (!$rClass->implementsInterface(IEditable::class)) {
      throw new TypeError("Class is not IEditable: $className");
    }

    if ($rClass->isAbstract()) {
      if (!$rClass->implementsInterface(ISubtyped::class)) {
        throw new TypeError("Class is abstract and not ISubtyped");
      }
      /** @var class-string<IEditable&ISubtyped> $className */
      $this->subtypes = $className::getSubtypes();
    }

    $constructorParams = $rClass->getConstructor()?->getParameters();
    if ($constructorParams) { // An empty array is falsy in php
      throw new TypeError("Class has a constructor with at least one parameter: $className");
    }

    foreach ($rClass->getProperties(ReflectionProperty::IS_PUBLIC) as $rProp) {
      if ($rProp->isStatic()) continue;
      $docComment = $rProp->getDocComment();
      if (!$docComment) $docComment = "";

      preg_match_all(
        '/@(\S+) ?(.*)\n?/',
        str_replace("\r\n", "\n", $docComment),
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
        "annotations" => $annotations,
        "prop_ref" => $rProp,
      ];

      $this->formResult[$rProp->getName()] = $default ? $rProp->getValue($default) : null;
    }
  }

  protected function createForm(Player $player): Form
  {
    $className = substr(
      $this->getAnnotationNonNull("class"),
      strrpos($this->getAnnotationNonNull("class"), "\\", -1) + 1
    );

    if (isset($this->subtypes)) {
      $options = [];

      foreach ($this->subtypes as $class) {
        $color = ($this->getDefault() instanceof $class) ? "§2" : "§0";
        $slashPos = strrpos($class, "\\", -1);
        $prettyName = substr($class, $slashPos ? $slashPos + 1 : 0);
        $options[] = new MenuOption($color . $prettyName);
      }

      return new MenuForm(
        $this->getAnnotation("label") ?? $className,
        ($this->getAnnotation("description") ?? "Choose a version of $className"),
        $options,
        function (Player $player, int $selectedOption): void {
          $annotations = $this->annotations;
          /** @phpstan-var array{class: class-string<T>}&array<string, string> $annotations */
          $annotations["class"] = $this->subtypes[$selectedOption];
          /** 
           * @var IEditable|null
           * @phpstan-var T|null
           */
          $default = $this->getDefault();
          $editor = new self($annotations, $default);
          $editor->onFinish(function (?IEditable $value): void {
            $this->setFinished($value); // Don't double navigate to previous
          });
          $editor->sendTo($player); // Send Editor without FormSession history
        },
        function (Player $player): void {
          $this->setFinished(null, $player);
        }
      );
    }
    /** @var string[] */
    $indexToPropName = [];
    $options = [];
    foreach ($this->formResult as $prop => $value) {
      $indexToPropName[] = $prop;
      $type = $this->types[$prop]["type"];
      $valueString = $this->getTypedString($type, $value);
      $nameColor = $value === null ? "§c" : "§2";
      $options[] = new MenuOption($nameColor . ucfirst($prop) . "§r is $valueString");
    }

    $options[] = new MenuOption("Submit");
    $option_count = count($options);

    return new MenuForm(
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
            /** @var string $prop */
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
  }

  private function getPropType(string $propName): string
  {
    return $this->types[$propName]["type"];
  }

  /** @return string[] */
  private function getPropAnnotations(string $propName): array
  {
    return $this->types[$propName]["annotations"];
  }

  private function getPropReflection(string $propName): ReflectionProperty
  {
    return $this->types[$propName]["prop_ref"];
  }

  private function tryFinish(Player $player): void
  {
    /** 
     * @var class-string<self> $class
     * @phpstan-var class-string<T> $class
     */
    $class = $this->getAnnotationNonNull("class");
    $object = new $class;
    foreach (array_keys($this->types) as $prop) {
      if ($this->formResult[$prop] === null) {
        $this->error = true;
        $this->sendTo($player);
        return;
      }
      $this->getPropReflection($prop)->setValue($object, $this->formResult[$prop]);
    }
    $this->setFinished($object, $player);
  }

  /** @return mixed[] */
  protected function getDefaultAnnotations(): array
  {
    return self::DEFAULT_ANNOTATIONS;
  }
}
