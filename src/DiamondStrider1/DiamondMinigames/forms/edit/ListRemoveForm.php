<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\form\Form;
use pocketmine\math\Vector3;
use pocketmine\Player;

class ListRemoveForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"               => "List",
    "description"         => "Edit the List.",
    "element_type"        => "",
    "element_annotations" => "",
  ];

  private array $editedValue;

  public function __construct(array $annotations = [], $default = null)
  {
    parent::__construct($annotations, $default);
    $this->editedValue = $default ?? [];
  }

  protected function createForm(Player $player): Form
  {
    $options = [];

    foreach ($this->editedValue as $value) {
      $typeName = ucfirst($this->getAnnotation("element_type"));
      switch ($this->getAnnotation("element_type")) {
        case "string":
        case "boolean":
        case "integer":
        case "float":
          $options[] = new MenuOption((string) $value);
          break;
        case "vector":
        case "position":
          /** @var Vector3 $value */
          [$x, $y, $z] = [$value->getX(), $value->getY(), $value->getZ()];
          $options[] = new MenuOption("$typeName($x, $y, $z)");
          break;
        case "list":
          $options[] = new MenuOption("List [...]");
          break;
        default:
          $options[] = new MenuOption($typeName);
      }
    }

    $options[] = new MenuOption("Finish Removing");
    $option_count = count($options);

    $form = new MenuForm(
      $this->getAnnotation("label"),
      $this->getAnnotation("description"),
      $options,
      function (Player $player, int $selectedOption) use ($option_count): void {
        switch ($selectedOption) {
          case $option_count - 1:
            $this->setFinished($this->editedValue, $player);
            break;
          default:
            unset($this->editedValue[$selectedOption]);
            $this->editedValue = array_values($this->editedValue);
            $this->sendTo($player);
        }
      },
      function (Player $player): void {
        $this->setFinished($this->editedValue, $player);
      }
    );

    return $form;
  }

  private function getElementAnnotations(): array
  {
    $result = [];
    $annotations = explode(", ", $this->getAnnotation("element_annotations"));
    foreach ($annotations as $annotation) {
      if (strpos($annotation, " => ") === false) {
        continue;
      }
      [$name, $value] = explode(" => ", $annotation, 2);
      $result[$name] = $value;
    }
    return $result;
  }

  protected function getDefaultAnnotations(): array
  {
    return self::DEFAULT_ANNOTATIONS;
  }
}
