<?php
/**
 * @package Mediboard\Core\Requirements
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module\Requirements;

use Countable;
use Exception;
use Iterator;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReturnTypeWillChange;

/**
 * Class CRequirementsManager
 */
abstract class CRequirementsManager implements Countable, Iterator {

  const TYPE_EXPECTED_EQUALS_OR_GREATER = 'equalsOrGreater';
  const TYPE_EXPECTED_EQUALS_OR_LESS = 'equalsOrLess';
  const TYPE_EXPECTED_NOTNULL = 'notNull';
  const TYPE_EXPECTED_REGEX = 'regex';
  const TYPE_EXPECTED_BOOL = 'bool';
  const TYPE_EXPECTED_NOT_EQUALS = 'notEquals';

  /** @var CRequirementsItem[] */
  private $items = [];

  /** @var array */
  private $tabs = [];

  /** @var array */
  private $groups = [];

  /** @var int */
  private $position = 0;

  /** @var CRequirementsDescription */
  private $description;

  /** @var int */
  private $items_index = 0;

  /**
   * @var CGroups
   */
  protected $establishment;

  /**
   * @param CGroups $establishment
   *
   * @return void
   */
  public function setEstablishment(CGroups $establishment): void {
    $this->establishment = $establishment;
  }

  /**
   * @return CGroups
   */
  public function getEstablishment(): CGroups {
    return $this->establishment;
  }

  /**
   * @param CGroups|null $establishment
   *
   * @return bool
   * @throws ReflectionException
   */
  final public function checkRequirements(?CGroups $establishment = null) {
    $this->establishment = $establishment;
    if (!$this->establishment) {
      $this->establishment = CGroups::loadCurrent();
    }
    $reflection = new ReflectionClass($this);
    $methods    = $reflection->getMethods();

    // Reflection on trait
    $methodsTrait = array_merge([], ...array_values(array_map(function ($reflection_trait) {
      /** @var ReflectionClass $reflection_trait */
      return $reflection_trait->getMethods();
    }, $reflection->getTraits())));

    // methods in traits
    $methodNamesTrait = array_map(function ($method) {
      /** @var ReflectionMethod $method */
      return $method->getName();
    }, $methodsTrait);

    foreach ($methods as $method) {
      if (strpos($method->getName(), 'check') !== 0) {
        // Only checkLoremIpsum methods
        continue;
      }
      if ($method->class !== get_class($this) || in_array($method->getName(), $methodNamesTrait)) {
        // Ignore parent methods (current!!)
        continue;
      }

      $method->invoke($this);

      // set annotation
      $doc = $method->getDocComment();

      $group = $this->getTag($doc, "group");
      if ($group) {
        !array_key_exists($group, $this->tabs) ? $this->groups[$group] = 1 : $this->groups[$group]++;
      }

      $tab = $this->getTag($doc, "tab");
      if ($tab) {
        !array_key_exists($tab, $this->tabs) ? $this->tabs[$tab] = 1 : $this->tabs[$tab]++;;
      }

      if ($group === null && $tab === null) {
        continue;
      }

      /** @var CRequirementsItem $item */
      foreach ($this->items as $key => $item) {
        if ($key < $this->items_index) {
          continue;
        }

        if ($tab) {
          $item->setTab($tab);
        }

        if ($group) {
          $item->setGroup($group);
        }
      }

      $this->items_index = count($this->items);
    }

    return $this->countErrors() === 0;
  }

  /**
   * @param string $documentation
   * @param string $tag
   *
   * @return string|null
   */
  private function getTag(string $documentation, string $tag):? string {
    $tag = trim($tag);
    $re = "/(?:@$tag (?'$tag'[\w|[[:blank:]]+))/m";
    preg_match_all($re, $documentation, $matches, PREG_PATTERN_ORDER, 0);

    return empty($matches[$tag]) ? null : strtolower(trim(reset($matches[$tag])));
  }

  /**
   * @param array $modules
   *
   * @return void
   */
  protected function assertModulesActived(array $modules): void {
    foreach ($modules as $module_name) {
      $module = CModule::getInstalled($module_name);
      $actual = $module && $module->mod_active ? true : false;
      $check  = true === $actual;
      $item   = new CRequirementsItem(true, $actual, $check);
      $item->setDescription("module-$module_name-court");

      $this->addItems($item);
    }
  }

  /**
   * @param CModelObject $object
   * @param string       $field
   * @param string|null  $description
   *
   * @return void
   */
  protected function assertObjectFieldNotNull(CModelObject $object, string $field, ?string $description = null): void {
    $this->assertObjectFieldCheck($object, $field, self::TYPE_EXPECTED_NOTNULL, $description, self::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CModelObject $object
   * @param string       $field
   * @param string|null  $description
   *
   * @return void
   */
  protected function assertObjectFieldTrue(CModelObject $object, string $field, ?string $description = null): void {
    $this->assertObjectFieldCheck($object, $field, true, $description, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CModelObject $object
   * @param string       $field
   * @param string|null  $description
   *
   * @return void
   */
  protected function assertObjectFieldFalse(CModelObject $object, string $field, ?string $description = null): void {
    $this->assertObjectFieldCheck($object, $field, false, $description, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CModelObject $object
   * @param string       $field
   * @param mixed        $expected
   * @param string|null  $description
   *
   * @return void
   */
  protected function assertObjectFieldEquals(CModelObject $object, string $field, $expected, ?string $description = null): void {
    $this->assertObjectFieldCheck($object, $field, $expected, $description, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CModelObject $object
   * @param string       $field
   * @param mixed        $expected
   * @param string|null  $description
   * @param string|null  $type
   *
   * @return void
   */
  private function assertObjectFieldCheck(CModelObject $object, string $field, $expected, ?string $description = null, ?string $type = null): void {
    $actual = $object->$field;
    $check  = $this->check($actual, $expected, $type);
    $item   = new CRequirementsItem($expected, $actual, $check);
    if ($field === '_id' && !$description) {
      $field = $object->_spec->key ?? $field;
    }

    if (!$description) {
      $description = $object->_class . '-' . $field . "[$object->_class]";
        $item->setDescription($description);
    } else {
        $item->setDescription($description, false);
    }

    $this->addItems($item);
  }

  /**
   * @param mixed  $actual
   * @param string $description
   *
   * @return void
   */
  protected function assertNotNull($actual, string $description): void {
    $this->assertCheck($actual, self::TYPE_EXPECTED_NOTNULL, $description, self::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param mixed  $actual
   * @param string $description
   *
   * @return void
   */
  protected function assertTrue($actual, string $description): void {
    $this->assertCheck($actual, true, $description, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param mixed  $actual
   * @param string $description
   *
   * @return void
   */
  protected function assertFalse($actual, string $description): void {
    $this->assertCheck($actual, false, $description, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param mixed  $actual
   * @param mixed  $expected
   * @param string $description
   *
   * @return void
   */
  protected function assertEquals($actual, $expected, string $description): void {
    $this->assertCheck($actual, $expected, $description);
  }

  /**
   * @param mixed  $actual
   * @param mixed  $expected
   * @param string $description
   *
   * @return void
   */
  protected function assertNotEquals($actual, $expected, string $description): void {
    $this->assertCheck($actual, $expected, $description, self::TYPE_EXPECTED_NOT_EQUALS);
  }

  /**
   * @param mixed  $actual
   * @param string $regex
   * @param string $description
   *
   * @return void
   */
  protected function assertRegex($actual, string $regex, string $description): void {
    $this->assertCheck($actual, $regex, $description, self::TYPE_EXPECTED_REGEX);
  }

  /**
   * @param mixed       $actual
   * @param string      $expected
   * @param string      $description
   * @param string|null $type
   *
   * @return void
   */
  private function assertCheck($actual, $expected, string $description, ?string $type = null): void {
    $check = $this->check($actual, $expected, $type);
    $item  = new CRequirementsItem($expected, $actual, $check);
    $item->setDescription($description, false);

    $this->addItems($item);
  }

  /**
   * @param CMbObject $object
   * @param string    $field
   * @param mixed     $expected
   *
   * @return void
   */
  protected function assertObjectConfEquals(?CMbObject $object, string $field, $expected): void {
    $this->assertObjectConfCheck($object, $field, $expected);
  }

  /**
   * @param CMbObject $object
   * @param string    $field
   *
   * @return void
   */
  protected function assertObjectConfTrue(?CMbObject $object, string $field): void {
    $this->assertObjectConfCheck($object, $field, true, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CMbObject $object
   * @param string    $field
   *
   * @return void
   */
  protected function assertObjectConfFalse(?CMbObject $object, string $field): void {
    $this->assertObjectConfCheck($object, $field, false, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param CMbObject $object
   * @param string    $field
   * @param string    $regex
   *
   * @return void
   */
  protected function assertObjectConfRegex(?CMbObject $object, string $field, string $regex): void {
    $this->assertObjectConfCheck($object, $field, $regex, self::TYPE_EXPECTED_REGEX);
  }

  /**
   * @param CMbObject $object
   * @param string    $field
   *
   * @return void
   */
  protected function assertObjectConfNotNull(?CMbObject $object, string $field): void {
    $this->assertObjectConfCheck($object, $field, self::TYPE_EXPECTED_NOTNULL, self::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param CMbObject   $object
   * @param string      $field
   * @param mixed       $expected
   * @param string|null $type
   *
   * @return void
   */
  private function assertObjectConfCheck(CMbObject $object, string $field, $expected, ?string $type = null): void {
    if (!$object->_ref_object_configs) {
      $object->loadRefObjectConfigs();
    }
    $actual = $object->_ref_object_configs ? $object->_ref_object_configs->$field : null;
    $check  = $this->check($actual, $expected, $type);
    $item   = new CRequirementsItem($expected, $actual, $check);
    $item->setDescription($object->_ref_object_configs->_class . '-' . $field);
    $this->addItems($item);
  }

  /**
   * @param string $path
   * @param mixed  $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertConfEquals(string $path, $expected): void {
    $this->assertConf($path, $expected);
  }

  /**
   * @param string $path
   * @param int    $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertConfEqualsOrGreater(string $path, int $expected): void {
    $this->assertConf($path, $expected, self::TYPE_EXPECTED_EQUALS_OR_GREATER);
  }

  /**
   * @param string $path
   * @param int    $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertConfEqualsOrLess(string $path, int $expected): void {
    $this->assertConf($path, $expected, self::TYPE_EXPECTED_EQUALS_OR_LESS);
  }

  /**
   * @param string $path
   * @param int    $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertGConfEqualsOrGreater(string $path, int $expected): void {
    $this->assertConf($path, $expected, self::TYPE_EXPECTED_EQUALS_OR_GREATER, true);
  }

  /**
   * @param string $path
   * @param int    $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertGConfEqualsOrLess(string $path, int $expected): void {
    $this->assertConf($path, $expected, self::TYPE_EXPECTED_EQUALS_OR_LESS, true);
  }

  /**
   * @param string $path
   *
   * @return void
   * @throws Exception
   */
  protected function assertConfTrue(string $path): void {
    $this->assertConf($path, true, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param string $path
   *
   * @return void
   * @throws Exception
   */
  protected function assertConfFalse(string $path): void {
    $this->assertConf($path, false, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param string $path
   *
   * @return void
   * @throws Exception
   */
  protected function assertConfNotNull(string $path): void {
    $this->assertConf($path, self::TYPE_EXPECTED_NOTNULL, self::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param string $path
   *
   * @return void
   * @throws Exception
   */
  protected function assertGConfNotNull(string $path): void {
    $this->assertGConf($path, self::TYPE_EXPECTED_NOTNULL, self::TYPE_EXPECTED_NOTNULL);
  }

  /**
   * @param string $path
   * @param mixed  $expected
   *
   * @return void
   * @throws Exception
   */
  protected function assertGConfEquals(string $path, $expected): void {
    $this->assertGConf($path, $expected);
  }

  /**
   * @param string $path
   *
   * @return void
   * @throws Exception
   */
  protected function assertGConfTrue(string $path): void {
    $this->assertGConf($path, true, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param string $path
   *
   * @return void
   * @throws Exception
   */
  protected function assertGConfFalse(string $path): void {
    $this->assertGConf($path, false, self::TYPE_EXPECTED_BOOL);
  }

  /**
   * @param string $path
   * @param string $regex
   *
   * @return void
   * @throws Exception
   */
  protected function assertGConfRegex(string $path, string $regex): void {
    $this->assertGConf($path, $regex, self::TYPE_EXPECTED_REGEX);
  }

  /**
   * @param string      $path
   * @param mixed       $expected
   * @param null|string $type
   *
   * @return void
   * @throws Exception
   */
  private function assertGConf(string $path, $expected, ?string $type = null) {
    $this->assertConf($path, $expected, $type, true);
  }

  /**
   * @param string      $path
   * @param mixed       $expected
   * @param null|string $type
   * @param bool        $gconf
   *
   * @return void
   * @throws Exception
   */
  private function assertConf(string $path, $expected, ?string $type = null, bool $gconf = false) {
    if ($gconf) {
      $actual = CAppUI::gconf($path, $this->establishment->group_id);
    }
    else {
      $actual = CAppUI::conf($path);
    }

    $check = $this->check($actual, $expected, $type);

    // item
    $item    = new CRequirementsItem($expected, $actual, $check);
    $explode = explode(' ', $path);
    $desc    = 'config-' . implode('-', $explode);

    $item->setDescription($desc);
    $item->setSection(count($explode) > 0 ? $explode[0] : 'system');
    $this->addItems($item);
  }

  /**
   * @param mixed  $actual
   * @param mixed  $expected
   * @param string $type
   *
   * @return bool
   */
  protected final function check($actual, $expected, ?string $type = null) {
    switch ($type) {
      case self::TYPE_EXPECTED_NOTNULL:
          if (is_string($actual)) {
              return $actual !== "";
          }
        return $actual !== null;

      case self::TYPE_EXPECTED_BOOL:
        return $actual == $expected && $actual !== null;

      case self::TYPE_EXPECTED_REGEX:
        return preg_match($expected, $actual) == true;

      case self::TYPE_EXPECTED_NOT_EQUALS:
        return $actual !== $expected;

      case self::TYPE_EXPECTED_EQUALS_OR_GREATER:
        return $actual >= $expected;

      case self::TYPE_EXPECTED_EQUALS_OR_LESS:
        return $actual <= $expected;

      default:
        return $actual === $expected;
    }
  }

  /**
   * Get descriptor object
   *
   * @return CRequirementsDescription
   */
  public function getDescription(): CRequirementsDescription {
    $this->description = new CRequirementsDescription();

    return $this->description;
  }


    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

  /**
   * @return int
   */
  public function countErrors(): int {
    $count = 0;
    foreach ($this->items as $item) {
      if (!$item->isCheck()) {
        $count++;
      }
    }

    return $count;
  }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }


    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * @return bool
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->items    = array_values($this->items);
    }

  /**
   * @param mixed $new_items
   */
  protected function addItems($new_items) {
    $new_items   = is_array($new_items) ? $new_items : [$new_items];
    $this->items = array_merge($this->items, $new_items);
  }

  /**
   * @param bool $is_grouped
   *
   * @return array
   */
  public function serialize($is_grouped = true): array {
    $datas = [];

    foreach ($this->items as $item) {
      if ($is_grouped) {
        $current_tab     = $item->getTab();
        $current_group   = $item->getGroup();
        $current_section = $item->getSection();

        // datas
        $datas[$current_tab][$current_group][$current_section][] = [
          'expected'    => $item->getExpected(),
          'actual'      => $item->getActual(),
          'description' => $item->getDescription(),
          'check'       => $item->isCheck(),
        ];
      }
      else {
        $datas[] = $item->serialize();
      }
    }

    return $datas;
  }

  /**
   * @return array
   */
  public function getTabs() {
    return array_keys($this->serialize());
  }

  /**
   * @return array
   */
  public function getGroups() {
    return array_keys($this->groups);
  }

  /**
   * @return CRequirementsItem[]
   */
  public function getItems() {
    return $this->items;
  }
}
