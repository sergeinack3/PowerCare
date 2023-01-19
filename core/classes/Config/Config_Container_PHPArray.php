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
 * Lightweight compatibility reimplementation of PEAR_Config PHPArray parser
 */
final class Config_Container_PHPArray {
  private $options = array(
    'name' => 'dPconfig',
  );

  public function __construct($options = array()) {
    $this->options = array_merge($this->options, $options);
  }

  /**
   * Parses the data of the given configuration file
   *
   * @param string|array $datasrc Path to the configuration file
   * @param Config       $obj     Config object
   *
   * @return bool
   * @throws Exception
   */
  public function parseDatasrc($datasrc, Config $obj) {
    $return = true;

    if (empty($datasrc)) {
      throw new Exception('Datasource file path is empty.');
    }

    if (is_array($datasrc)) {
      $this->_parseArray($datasrc, $obj->getContainer());
    }
    else {
      if (!file_exists($datasrc)) {
        throw new Exception('Datasource file does not exist.');
      }

      include $datasrc;

      if (!isset(${$this->options['name']}) || !is_array(${$this->options['name']})) {
        throw new Exception("File '$datasrc' does not contain a required '{$this->options['name']}' array.");
      }

      $this->_parseArray(${$this->options['name']}, $obj->getContainer());
    }

    return $return;
  }

  /**
   * Parses the PHP array recursively
   *
   * @param array            $array     Array values from the config file
   * @param Config_Container $container Reference to the container object
   *
   * @throws Exception
   */
  private function _parseArray($array, Config_Container $container) {
    foreach ($array as $key => &$value) {
      if (is_array($value)) {
        $section = $container->createSection($key);
        $this->_parseArray($value, $section);
      }
      else {
        $container->createDirective($key, $value);
      }
    }
  }

  /**
   * Returns a formatted string of the object
   *
   * @param Config_Container $obj Container object to be output as string
   *
   * @return string
   */
  public function toString(Config_Container $obj) {
    $string = '';

    switch ($obj->type) {
      case 'directive':
        $parentString = $this->_getParentString($obj);
        $string       .= $parentString;

        $string .= ' = ';

        if (is_string($obj->content)) {
          $string .= "'" . addcslashes($obj->content, "\\'") . "'";
        }
        elseif (is_int($obj->content) || is_float($obj->content)) {
          $string .= $obj->content;
        }
        elseif (is_bool($obj->content)) {
          $string .= ($obj->content) ? 'true' : 'false';
        }
        elseif ($obj->content === null) {
          $string .= 'null';
        }

        $string .= ";\n";
        break;

      case 'section':
        if ($count = count($obj->children)) {
          for ($i = 0; $i < $count; $i++) {
            $string .= $this->toString($obj->getChild($i));
          }
        }
        break;

      default:
        $string = '';
    }

    return $string;
  }

  /**
   * Returns a formatted string of the object parents
   *
   * @param Config_Container $obj
   *
   * @return string
   */
  private function _getParentString(Config_Container $obj) {
    $string = '';

    if (!$obj->isRoot()) {
      $string = is_int($obj->name) ? "[{$obj->name}]" : "['{$obj->name}']";
      $string = $this->_getParentString($obj->parent) . $string;
      $count  = $obj->parent->countChildren(null, $obj->name);

      if ($count > 1) {
        $string .= '[' . $obj->getItemPosition() . ']';
      }
    }
    else {
      if (empty($this->options['name'])) {
        $string .= "\${$obj->name}";
      }
      else {
        $string .= "\${$this->options['name']}";
      }
    }

    return $string;
  }

  /**
   * Writes the configuration to a file
   *
   * @param string|array     $datasrc Datasource such as path to the configuraton file
   * @param Config_Container $obj     Container to write from
   *
   * @return bool
   * @throws Exception
   */
  public function writeDatasrc($datasrc, Config_Container $obj) {
    $fp = fopen($datasrc, 'w');

    if (!$fp) {
      throw new Exception('Cannot open datasource for writing');
    }

    $string = "<?php\n{$this->toString($obj)}";
    $len    = strlen($string);

    @flock($fp, LOCK_EX);
    @fwrite($fp, $string, $len);
    @flock($fp, LOCK_UN);
    @fclose($fp);

    return true;
  }
}