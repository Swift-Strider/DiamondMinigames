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

  /** @var mixed[] */
  private array $formResult;

  /** @param ?mixed[] $default */
  public function __construct(array $annotations = [], array $default = null)
  {
    parent::__construct($annotations, $default);
    $this->formResult = $default ?? [];
  }

  protected function createForm(Player $player): Form
  {
    $options = [];

    foreach ($this->formResult as $value) {
      $typeName = ucfirst($this->getAnnotationNonNull("element_type"));
      switch ($this->getAnnotation("element_type")) {
        case "string":
        case "boolean":
        case "integer":
        case "float":
          /** @var string|bool|int|float $value */
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
      $this->getAnnotationNonNull("label"),
      $this->getAnnotationNonNull("description"),
      $options,
      function (Player $player, int $selectedOption) use ($option_count): void {
        switch ($selectedOption) {
          case $option_count - 1:
            $this->setFinished($this->formResult, $player);
            break;
          default:
            unset($this->formResult[$selectedOption]);
            $this->formResult = array_values($this->formResult);
            $this->sendTo($player);
        }
      },
      function (Player $player): void {
        $this->setFinished($this->formResult, $player);
      }
    );

    return $form;
  }

  /** @return string[] */
  protected function getDefaultAnnotations(): array
  {
    return self::DEFAULT_ANNOTATIONS;
  }
}
