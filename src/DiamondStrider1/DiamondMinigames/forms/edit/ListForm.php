<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\form\Form;
use pocketmine\math\Vector3;
use pocketmine\Player;

class ListForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"               => "List",
    "description"         => "Edit the List.",
    "element_type"        => "",
    "element_annotations" => "",
  ];

  private array $formResult;

  public function __construct(array $annotations = [], $default = null)
  {
    parent::__construct($annotations, $default);
    $this->formResult = $default ?? [];
  }

  protected function createForm(Player $player): Form
  {
    $options = [
      new MenuOption("Add Element"),
      new MenuOption("Remove Element"),
    ];

    foreach ($this->formResult as $value) {
      $typeName = ucfirst($this->getAnnotation("element_type"));
      switch ($this->getAnnotation("element_type")) {
        case "string":
        case "boolean":
        case "integer":
        case "float":
          $options[] = new MenuOption((string) $value);
          break;
        case "vector":
          /** @var Vector3 $value */
          [$x, $y, $z] = [$value->getX(), $value->getY(), $value->getZ()];
          $options[] = new MenuOption("Vector($x, $y, $z)");
          break;
        case "list":
          $options[] = new MenuOption("List [...]");
          break;
        case "object":
          $objectClass = $this->getElementAnnotations()["class"];
          $objectName = substr($objectClass, strrpos($objectClass, "\\", -1) + 1);
          $options[] = new MenuOption("$objectName");
          break;
        default:
          $options[] = new MenuOption($typeName);
      }
    }

    $options[] = new MenuOption("Submit List");
    $option_count = count($options);

    $form = new MenuForm(
      $this->getAnnotation("label"),
      $this->getAnnotation("description"),
      $options,
      function (Player $player, int $selectedOption) use ($option_count): void {
        $element_index = $selectedOption - 2;
        switch ($selectedOption) {
          case 0:
            $editor = EditForm::build(
              $this->getAnnotation("element_type"),
              $this->getElementAnnotations()
            );
            $editor->onFinish(function ($value): void {
              if ($value === null) return;
              $this->formResult[] = $value;
            });
            $this->openForm($player, $editor);
            break;
          case 1:
            $editor = new ListRemoveForm($this->annotations, $this->formResult);
            $editor->onFinish(function ($value): void {
              $this->formResult = $value;
            });
            $this->openForm($player, $editor);
            break;
          case $option_count - 1:
            $this->setFinished($this->formResult, $player);
            break;
          default:
            $editor = EditForm::build(
              $this->getAnnotation("element_type"),
              $this->getElementAnnotations(),
              $this->formResult[$element_index]
            );
            $editor->onFinish(function ($value) use ($element_index): void {
              if ($value === null) return;
              $this->formResult[$element_index] = $value;
            });
            $this->openForm($player, $editor);
        }
      },
      function (Player $player): void {
        $this->setFinished($this->formResult, $player);
      }
    );

    return $form;
  }

  private function getElementAnnotations(): array
  {
    $result = [];
    $annotations = explode(", ", $this->getAnnotation("element_annotations"));
    foreach ($annotations as $annotation) {
      if (strpos($annotation, "=") === false) {
        continue;
      }
      [$name, $value] = explode("=", $annotation, 2);
      $result[$name] = $value;
    }
    return $result;
  }

  protected function getDefaultAnnotations(): array
  {
    return self::DEFAULT_ANNOTATIONS;
  }
}
