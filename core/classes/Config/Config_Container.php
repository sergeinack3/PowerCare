<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Config;

use Exception;

/**
 * Lightweight compatibility reimplementation of PEAR_Config Config_Container
 */
final class Config_Container {
  /** @var string Container object type (section, directive) */
  public $type;

  /** @var string Container object name */
  public $name = '';

  /** @var string Container object content */
  public $content = '';

  /** @var Config_Container[] Container object children */
  public $children = array();

  /** @var Config_Container Reference to container object's parent */
  public $parent;

  /**
   * Constructor
   *
   * @param  string $type    Type of container object
   * @param  string $name    Name of container object
   * @param  string $content Content of container object
   */
  public function __construct($type = 'section', $name = '', $content = '') {
    $this->type    = $type;
    $this->name    = $name;
    $this->content = $content;
    $this->parent  = null;
  }

  /**
   * Create a child for this item.
   *
   * @param  string $type    Type of item: directive, section
   * @param  mixed  $name    Item name
   * @param  string $content Item content
   *
   * @return Config_Container
   * @throws Exception
   */
  private function createItem($type, $name, $content) {
    $item   = new Config_Container($type, $name, $content);
    $result = $this->addItem($item);

    return $result;
  }

  /**
   * Adds an item to this item.
   *
   * @param Config_Container $item A container object
   *
   * @return Config_Container Added container on success
   * @throws Exception
   */
  private function addItem(Config_Container $item) {
    if ($this->type !== 'section') {
      throw new Exception('Config_Container::addItem must be called on a section type object.');
    }

    // Places at the bottom
    $index = count($this->children);

    $this->children[$index]         = $item;
    $this->children[$index]->parent = $this;

    return $item;
  }

  /**
   * Adds a directive to this item.
   * This is a helper method that calls createItem
   *
   * @param string $name    Name of new directive
   * @param string $content Content of new directive
   *
   * @return Config_Container
   * @throws Exception
   */
  public function createDirective($name, $content) {
    return $this->createItem('directive', $name, $content);
  }

  /**
   * Adds a section to this item.
   *
   * This is a helper method that calls createItem
   * If the section already exists, it won't create a new one.
   * It will return reference to existing item.
   *
   * @param string $name Name of new section
   *
   * @return Config_Container
   * @throws Exception
   */
  public function createSection($name) {
    return $this->createItem('section', $name, null);
  }

  /**
   * Returns how many children this container has
   *
   * @param string $type Type of children counted
   * @param string $name Name of children counted
   *
   * @return int Number of children found
   */
  public function countChildren($type = null, $name = null) {
    if (is_null($type) && is_null($name)) {
      return count($this->children);
    }

    $children = count($this->children);
    $count    = 0;

    if (isset($name) && isset($type)) {
      for ($i = 0; $i < $children; $i++) {
        if ($this->children[$i]->name === $name && $this->children[$i]->type == $type) {
          $count++;
        }
      }

      return $count;
    }

    if (isset($type)) {
      for ($i = 0; $i < $children; $i++) {
        if ($this->children[$i]->type == $type) {
          $count++;
        }
      }

      return $count;
    }

    if (isset($name)) {
      // Some directives can have the same name
      for ($i = 0; $i < $children; $i++) {
        if ($this->children[$i]->name === $name) {
          $count++;
        }
      }

      return $count;
    }

    return $count;
  }

  /**
   * Returns the item rank in its parent children array according to other items with same type and name.
   *
   * @return int|null Returns int or null if root object
   */
  public function getItemPosition() {
    if ($this->isRoot()) {
      return null;
    }

    $pchildren = $this->parent->children;
    $obj       = array();

    $count = count($pchildren);
    for ($i = 0; $i < $count; $i++) {
      if ($pchildren[$i]->name === $this->name) {
        $obj[] = $pchildren[$i];
      }
    }

    $count = count($obj);
    for ($i = 0; $i < $count; $i++) {
      if ($obj[$i] === $this) {
        return $i;
      }
    }

    return null;
  }

  /**
   * Returns the item parent object.
   *
   * @param int $index Index of child to get
   *
   * @return Config_Container|false Child object or false if child does not exist
   */
  public function getChild($index = 0) {
    if (!empty($this->children[$index])) {
      return $this->children[$index];
    }

    return false;
  }

  /**
   * Is this item root, in a config container object
   *
   * @return bool
   */
  public function isRoot() {
    return (is_null($this->parent));
  }

  /**
   * Returns a key/value pair array of the container and its children.
   *
   * Format : section[directive][index] = value
   * index is here because multiple directives can have the same name.
   *
   * @return array
   */
  public function toArray() {
    $array[$this->name] = array();

    switch ($this->type) {
      case 'directive':
        $array[$this->name] = $this->content;
        break;

      case 'section':
        if ($count = count($this->children)) {
          for ($i = 0; $i < $count; $i++) {
            $newArr = $this->children[$i]->toArray();

            if (!is_null($newArr)) {
              foreach ($newArr as $key => $value) {
                if (isset($array[$this->name][$key])) {
                  // duplicate name/type
                  if (!is_array($array[$this->name][$key]) || !isset($array[$this->name][$key][0])) {
                    $old = $array[$this->name][$key];
                    unset($array[$this->name][$key]);
                    $array[$this->name][$key][0] = $old;
                  }

                  $array[$this->name][$key][] = $value;
                }
                else {
                  $array[$this->name][$key] = $value;
                }
              }
            }
          }
        }
        break;

      default:
        return null;
    }

    return $array;
  }

  /**
   * Writes the configuration to a file
   *
   * @param string|array $datasrc Info on datasource such as path to the configuraton file or dsn...
   * @param array        $options Parser options
   *
   * @return bool
   * @throws Exception
   */
  public function writeDatasrc($datasrc, $options = array()) {
    $writer = new Config_Container_PHPArray($options);

    return $writer->writeDatasrc($datasrc, $this);
  }
}