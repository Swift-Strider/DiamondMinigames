<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use pocketmine\form\Form;
use pocketmine\math\Vector3;
use pocketmine\Player;

class VectorForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"       => "Vector",
    "description" => "Edit the Vector."
  ];

  private bool $error = false;
  private string $formResult = "";

  protected function createForm(Player $player): Form
  {
    $defaultVector = $this->getDefault();
    if ($defaultVector) {
      /** @var Vector3 $defaultVector */
      [$x, $y, $z] = array_map(function ($value) {
        return rtrim(rtrim(number_format($value, 6), "0"), ".");
      }, [$defaultVector->getX(), $defaultVector->getY(), $defaultVector->getZ()]);

      $default = "($x, $y, $z)";
    }
    $form = new CustomForm(
      $this->getAnnotationNonNull("label"),
      [
        new Label("description", $this->getAnnotationNonNull("description") . ($this->error ? "\n\nMistyped Vector (Position)" : "")),
        new Input("vector", "", "(3, 2, 1)", $this->error ? $this->formResult : ($defaultVector ? $default : "")),
      ],
      function (Player $player, CustomFormResponse $data): void {
        $matches = [];
        preg_match(
          '/(-?\d+(?:\.\d*)?) *, *(-?\d+(?:\.\d*)?) *, *(-?\d+(?:\.\d*)?)/',
          $data->getString("vector"),
          $matches
        );
        $rawVector = array_slice($matches, 1);
        if (count($rawVector) < 3) {
          $this->error = true;
          $this->formResult = $data->getString("vector");
          $this->sendTo($player);
          return;
        }
        $this->setFinished(new Vector3((float) $rawVector[0], (float) $rawVector[1], (float) $rawVector[2]), $player);
      },
      function (Player $player): void {
        $this->setFinished(null, $player);
      }
    );

    return $form;
  }

  /** @return mixed[] */
  protected function getDefaultAnnotations(): array
  {
    return self::DEFAULT_ANNOTATIONS;
  }
}
