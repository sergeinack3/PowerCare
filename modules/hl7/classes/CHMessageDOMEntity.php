<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Class CHL7v2MessageXML
 * Message XML HL7
 */
class CHMessageDOMEntity implements IShortNameAutoloadable {
  static $decorate = false;

  const TYPE_MESSAGE      = "message";
  const TYPE_SEGMENT      = "segment";
  const TYPE_FIELD        = "field";
  const TYPE_REPETITION   = "repetition";
  const TYPE_COMPONENT    = "component";
  const TYPE_SUBCOMPONENT = "subcomponent";

  static $depths = array(
    self::TYPE_MESSAGE      => 0,
    self::TYPE_SEGMENT      => 1,
    self::TYPE_FIELD        => 2,
    self::TYPE_REPETITION   => 3,
    self::TYPE_COMPONENT    => 4,
    self::TYPE_SUBCOMPONENT => 5,
  );

  static $separators = array(
    self::TYPE_MESSAGE      => "\n",
    self::TYPE_SEGMENT      => null,
    self::TYPE_FIELD        => null,
    self::TYPE_REPETITION   => null,
    self::TYPE_COMPONENT    => null,
    self::TYPE_SUBCOMPONENT => null,
  );

  /** @var int Type */
  public $type;

  /** @var string Glue */
  public $glue = "";

  /** @var self[] Children, if any */
  public $children = array();

  /** @var self */
  public $parent;

  /** @var string Self value */
  public $value;

  /**
   * CHMessageDOMEntity constructor.
   *
   * @param int                $type   Entity type
   * @param CHMessageDOMEntity $parent Parent entity
   */
  public function __construct($type, self $parent = null) {
    $this->type = $type;
    $this->glue = self::$separators[$type];

    if ($parent) {
      $parent->children[] = $this;
      $this->parent = $parent;
    }
  }

  /**
   * @param string $type      Entity type
   * @param string $separator Separator
   *
   * @return void
   */
  public static function setSeparator($type, $separator) {
    self::$separators[$type] = $separator;
  }

  /**
   * Appends a child into $this, into a sub child
   *
   * @param CHMessageDOMEntity $child Child to append
   * @param string             $key   Key to append the child to
   * @param string             $type  Type of the intermediate child
   *
   * @return CHMessageDOMEntity
   */
  public function appendSubChild(self $child, $key, $type) {
    if (!isset($this->children[$key])) {
      $this->children[$key] = new self($type);
      $this->children[$key]->parent = $this;
    }

    $this->children[$key]->children[] = $child;

    return $this->children[$key];
  }

  /**
   * ToString method, to flatten the structure
   *
   * @return string
   */
  public function __toString() {
    $pad_char = "--";

    if (count($this->children)) {
      if (self::$decorate) {
        $pad = "\n" . str_repeat($pad_char, self::$depths[$this->type]);

        return "{$pad}[$this->type]" . implode($pad, $this->children);
      }

      return implode($this->glue, $this->children);
    }

    if (self::$decorate) {
      $pad = "\n" . str_repeat($pad_char, self::$depths[$this->type]);

      return "{$pad}[$this->type] " . var_export($this->value, true);
    }

    return "$this->value";
  }

  /**
   * Find an entity among the descendants
   *
   * @param int $type Type of the entity to find
   *
   * @return CHMessageDOMEntity|null
   */
  public function find($type) {
    $entity = $this;

    while ($entity && $entity->type !== $type) {
      $entity = $entity->parent;
    }

    return $entity;
  }
}
