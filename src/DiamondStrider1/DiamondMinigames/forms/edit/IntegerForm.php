<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Slider;
use pocketmine\form\Form;
use pocketmine\Player;

/**
 * @extends EditForm<int|null>
 */
class IntegerForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"       => "Integer",
    "description" => "Edit the Integer.",
    "range"       => "any",
  ];

  protected function createForm(Player $player): Form
  {
    $range = $this->getAnnotationNonNull("range");
    $defaultInteger = $this->getDefault();
    if ($range !== "any") {
      [$min, $max] = explode(" - ", $range, 2);
      $element = new Slider(
        "number",
        "",
        (int) $min,
        (int) $max,
        1,
        $defaultInteger
      );
    } else {
      $element = new Input("number", "", "", (string) $defaultInteger);
    }
    $form = new CustomForm(
      $this->getAnnotationNonNull("label"),
      [
        new Label("description", $this->getAnnotationNonNull("description")),
        $element
      ],
      function (Player $player, CustomFormResponse $data): void {
        if ($this->getAnnotation("range") === "any") {
          $value = (int) $data->getString("number");
        } else {
          $value = (int) $data->getFloat("number");
        }
        $this->setFinished($value, $player);
      },
      function (Player $player): void {
        $this->setFinished(null, $player);
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
