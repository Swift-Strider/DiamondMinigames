<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use Closure;
use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use DomainException;
use LogicException;
use pocketmine\Player;
use pocketmine\utils\Utils;

abstract class EditForm extends BaseForm
{
  const EDIT_NAMESPACE = "DiamondStrider1\\DiamondMinigames\\forms\\edit\\";
  /** @var string[] $type => $class */
  private static array $formTypes = [
    "list"     => self::EDIT_NAMESPACE . "ListForm",
    "object"   => self::EDIT_NAMESPACE . "ObjectForm",
    "string"   => self::EDIT_NAMESPACE . "StringForm",
    "boolean"  => self::EDIT_NAMESPACE . "BooleanForm",
    "integer"  => self::EDIT_NAMESPACE . "IntegerForm",
    "float"    => self::EDIT_NAMESPACE . "FloatForm",
    "vector"   => self::EDIT_NAMESPACE . "VectorForm",
  ];

  /** @var string[] $name => $value */
  protected array $annotations;
  protected $default;
  /** @var Closure[] */
  private array $closures = [];

  public function __construct(array $annotations = [], $default = null)
  {
    $this->annotations = $annotations;
    $this->default = $default;
  }

  protected function setFinished($value, Player $player)
  {
    foreach ($this->closures as $cb) {
      $cb($value);
    }
    FormSessions::sendPrevious($player);
  }

  /**
   * @param Closure $cb Signature - function($value): void {}
   */
  public function onFinish(Closure $cb): void
  {
    Utils::validateCallableSignature(function ($value): void {
    }, $cb);
    $this->closures[] = $cb;
  }

  protected function getAnnotation(string $name): ?string
  {
    if (!array_key_exists($name, $this->getDefaultAnnotations())) {
      $class = get_class($this);
      throw new LogicException("$class does not provide a default for annotation @$name");
    }
    return $this->annotations[$name] ?? $this->getDefaultAnnotations()[$name];
  }

  protected function getDefault()
  {
    return $this->default;
  }

  abstract protected function getDefaultAnnotations(): array;


  /**
   * @param string $type
   * @param string[] $annotations $name => $value
   * @param any $default
   * @throws DomainException when $type is not registered
   */
  public static function build(string $type, array $annotations = [], $default = null): EditForm
  {
    if (!isset(self::$formTypes[$type])) {
      throw new DomainException("Type $type is not supported");
    }

    $class = self::$formTypes[$type];
    return new $class($annotations, $default);
  }

  /**
   * Adds a subclass of EditForm to the register.
   */
  public static function registerType(string $type, string $class)
  {
    self::$formTypes[$type] = $class;
  }
}
