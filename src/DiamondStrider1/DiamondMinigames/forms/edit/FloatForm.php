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

class FloatForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"       => "Float",
    "description" => "Edit the Float.",
    "range"       => "any",
  ];

  protected function createForm(Player $player): Form
  {
    /** @var ?float */
    $defaultFloat = $this->getDefault();
    $range = $this->getAnnotationNonNull("range");
    if ($range !== "any") {
      $step = 1;
      [$min, $max] = explode(" - ", $range, 2);
      if (strpos($max, " by ") !== false) {
        [$max, $step] = explode(" by ", $max, 2);
      }
      $element = new Slider(
        "number", "",
        (float) $min, (float) $max, (float) $step,
        $defaultFloat
      );
    } else {
      $element = new Input("number", "", "", (string) $defaultFloat);
    }
    $form = new CustomForm(
      $this->getAnnotationNonNull("label"),
      [
        new Label("description", $this->getAnnotationNonNull("description")),
        $element
      ],
      function (Player $player, CustomFormResponse $data): void {
        if ($this->getAnnotation("range") === "any") {
          $value = (float) $data->getString("number");
        } else {
          $value = (float) $data->getFloat("number");
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
