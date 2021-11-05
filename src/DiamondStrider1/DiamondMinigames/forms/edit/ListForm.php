<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\form\Form;
use pocketmine\Player;

/**
 * @extends EditForm<array<int, mixed>>
 */
class ListForm extends EditForm
{
  const DEFAULT_ANNOTATIONS = [
    "label"               => "List",
    "description"         => "Edit the List.",
    "element_type"        => "",
    "element_annotations" => "",
  ];

  /** @var mixed[] */
  private array $formResult;
  
  /**
   * @phpstan-param mixed[] $default
   */
  public function __construct(array $annotations = [], array $default = null)
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

    foreach ($this->formResult as $index => $value) {
      $typeName = ucfirst($this->getAnnotationNonNull("element_type"));
      $options[] = new MenuOption(sprintf(
        "§8#%d - §0%s",
        $index, $this->getTypedString($typeName, $value)
      ));
    }

    $options[] = new MenuOption("Submit List");
    $option_count = count($options);

    $form = new MenuForm(
      $this->getAnnotationNonNull("label"),
      $this->getAnnotationNonNull("description"),
      $options,
      function (Player $player, int $selectedOption) use ($option_count): void {
        $element_index = $selectedOption - 2;
        switch ($selectedOption) {
          case 0:
            $editor = EditForm::build(
              $this->getAnnotationNonNull("element_type"),
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
              $this->getAnnotationNonNull("element_type"),
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

  /** @return array<string, string> */
  private function getElementAnnotations(): array
  {
    /** @var array<string, string> */
    $result = [];
    $annotations = explode(", ", $this->getAnnotationNonNull("element_annotations"));
    foreach ($annotations as $annotation) {
      if (strpos($annotation, "=") === false) {
        continue;
      }
      [$name, $value] = explode("=", $annotation, 2);
      $result[$name] = $value;
    }
    return $result;
  }

  /** @return string[] */
  protected function getDefaultAnnotations(): array
  {
    return self::DEFAULT_ANNOTATIONS;
  }
}
