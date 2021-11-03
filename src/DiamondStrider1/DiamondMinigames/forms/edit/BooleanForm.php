<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Toggle;
use pocketmine\form\Form;
use pocketmine\Player;

class BooleanForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"       => "Boolean",
    "description" => "Edit the Boolean.",
  ];

  protected function createForm(Player $player): Form
  {
    /** @var ?bool */
    $defaultBoolean = $this->getDefault();
    $form = new CustomForm(
      $this->getAnnotationNonNull("label"),
      [
        new Toggle("toggle", $this->getAnnotationNonNull("description"), $defaultBoolean ?? false),
      ],
      function (Player $player, CustomFormResponse $data): void {
        $this->setFinished($data->getBool("toggle"), $player);
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
