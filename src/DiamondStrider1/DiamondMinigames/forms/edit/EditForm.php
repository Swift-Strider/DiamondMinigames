<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\forms\edit;

use Closure;
use DiamondStrider1\DiamondMinigames\forms\BaseForm;
use DiamondStrider1\DiamondMinigames\forms\FormSessions;
use DomainException;
use LogicException;
use pocketmine\math\Vector3;
use pocketmine\Player;
use TypeError;

/**
 * @phpstan-template T
 */
abstract class EditForm extends BaseForm
{
  const EDIT_NAMESPACE = "DiamondStrider1\\DiamondMinigames\\forms\\edit\\";
  /** @var array<string, class-string<self>>> */
  private static array $formTypes = [
    "list"     => self::EDIT_NAMESPACE . "ListForm",
    "object"   => self::EDIT_NAMESPACE . "ObjectForm",
    "string"   => self::EDIT_NAMESPACE . "StringForm",
    "boolean"  => self::EDIT_NAMESPACE . "BooleanForm",
    "integer"  => self::EDIT_NAMESPACE . "IntegerForm",
    "float"    => self::EDIT_NAMESPACE . "FloatForm",
    "vector"   => self::EDIT_NAMESPACE . "VectorForm",
  ];

  /** @var array<string, string> */
  protected array $annotations;
  /** 
   * @var mixed
   * @phpstan-var T
   */
  protected $default;
  /** 
   * @var Closure
   * @phpstan-var Closure(T): void
   */
  private ?Closure $callback = null;
  /**
   * @var bool when true setFinished() will automatically send the previous BaseForm
   */
  private bool $sendPrevious = true;

  /**
   * @param string[] $annotations
   * @phpstan-param T $default
   */
  public function __construct(array $annotations = [], mixed $default = null)
  {
    $this->annotations = $annotations;
    $this->default = $default;
  }

  /** @return string[] */
  abstract protected function getDefaultAnnotations(): array;

  /**
   * @phpstan-param T $value
   */
  protected function setFinished(mixed $value, Player $player = null): void
  {
    if ($this->callback) ($this->callback)($value);
    if ($player && $this->sendPrevious) FormSessions::sendPrevious($player);
  }

  /**
   * @param Closure $cb Signature - function($value): void {}
   * @phpstan-param Closure(T): void $cb
   * @param bool $sendPrevious Send previous form session automatically
   */
  public function onFinish(Closure $cb, bool $sendPrevious = true): void
  {
    $this->callback = $cb;
    $this->sendPrevious = $sendPrevious;
  }

  protected function getTypedString(string $type, mixed $value): string
  {
    if ($value === null) return "(UNFILLED)";
    switch ($type) {
      case "string":
        /** @var string $value */
        return $value;
      case "boolean":
        return $value ? "YES" : "NO";
      case "integer":
      case "float":
        /** @var int|float $value */
        return (string) $value;
      case "vector":
        if (!($value instanceof Vector3)) {
          throw new TypeError(sprintf(
            '$type is vector, but $value is a non-vector (%s)',
            (is_object($value) ? get_class($value) : gettype($value))
          ));
        }
        return sprintf(
          "Vector(%f, %f, %f)",
          $value->getX(),
          $value->getY(),
          $value->getZ()
        );
      case "list":
        return "List [...]";
      case "object":
        return "Object {...}";
      default:
        throw new TypeError("Invalid Type $type");
    }
  }

  protected function getAnnotationNonNull(string $name): string
  {
    $defaults = $this->getDefaultAnnotations();
    if (!isset($defaults[$name])) {
      $class = get_class($this);
      throw new LogicException("$class does not provide a default for annotation @$name");
    }
    return $this->annotations[$name] ?? $defaults[$name];
  }

  protected function getAnnotation(string $name): ?string
  {
    if (!array_key_exists($name, $this->getDefaultAnnotations())) {
      $class = get_class($this);
      throw new LogicException("$class does not provide a default for annotation @$name");
    }
    return $this->annotations[$name] ?? $this->getDefaultAnnotations()[$name];
  }

  /**
   * @phpstan-return T|null
   */
  protected function getDefault(): mixed
  {
    return $this->default;
  }

  /**
   * @param string $type
   * @param string[] $annotations $name => $value
   * @param mixed $default
   * @throws DomainException when $type is not registered
   * @return EditForm<mixed>
   */
  public static function build(string $type, array $annotations = [], $default = null): EditForm
  {
    if (!isset(self::$formTypes[$type])) {
      throw new DomainException("Type $type is not supported");
    }

    $class = self::$formTypes[$type];
    return new $class($annotations, $default);
  }
}
