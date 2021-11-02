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

class IntegerForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"       => "Integer",
    "description" => "Edit the Integer.",
    "range"       => "any",
  ];

  protected function createForm(Player $player): Form
  {
    $range = $this->getAnnotation("range");
    if ($range !== "any") {
      [$min, $max] = explode(" - ", $range, 2);
      $element = new Slider(
        "number",
        "",
        (int) $min,
        (int) $max,
        1,
        $this->getDefault() ?? (int) $min
      );
    } else {
      $element = new Input("number", "", "", (string) $this->getDefault());
    }
    $form = new CustomForm(
      $this->getAnnotation("label"),
      [
        new Label("description", $this->getAnnotation("description")),
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

  protected function getDefaultAnnotations(): array
  {
    return self::DEFAULT_ANNOTATIONS;
  }
}
