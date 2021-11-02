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
  private string $editedValue = "";

  protected function createForm(Player $player): Form
  {
    /** @var Vector3 $v */
    $v = $this->getDefault();
    if ($v) {
      [$x, $y, $z] = array_map(function ($value) {
        return rtrim(rtrim(number_format($value, 6), "0"), ".");
      }, [$v->getX(), $v->getY(), $v->getZ()]);

      $default = "($x, $y, $z)";
    }
    $form = new CustomForm(
      $this->getAnnotation("label"),
      [
        new Label("description", $this->getAnnotation("description") . ($this->error ? "\n\nMistyped Vector (Position)" : "")),
        new Input("vector", "", "(3, 2, 1)", $this->error ? $this->editedValue : ($v ? $default : "")),
      ],
      function (Player $player, CustomFormResponse $data): void {
        $matches = [];
        preg_match(
          '/(-?\d+(?:\.\d*)?) *, *(-?\d+(?:\.\d*)?) *, *(-?\d+(?:\.\d*)?)/',
          $data->getString("vector"), $matches
        );
        $v = array_slice($matches, 1);
        if (count($v) < 3) {
          $this->error = true;
          $this->editedValue = $data->getString("vector");
          $this->sendTo($player);
          return;
        }
        $this->setFinished(new Vector3((float) $v[0], (float) $v[1], (float) $v[2]), $player);
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
