<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use pocketmine\form\Form;
use pocketmine\Player;

class StringForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"       => "String",
    "description" => "Edit the String.",
    "placeholder" => "Type the String here"
  ];

  protected function createForm(Player $player): Form
  {
    /** @var ?string */
    $defaultString = $this->getDefault();
    $form = new CustomForm(
      $this->getAnnotationNonNull("label"),
      [
        new Label("description", $this->getAnnotationNonNull("description")),
        new Input("input", "", $this->getAnnotationNonNull("placeholder"), $defaultString ?? ""),
      ],
      function (Player $player, CustomFormResponse $data): void {
        $this->setFinished($data->getString("input"), $player);
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
