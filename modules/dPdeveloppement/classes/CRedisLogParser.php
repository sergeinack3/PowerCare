<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Redis log parser
 */
class CRedisLogParser extends CLogParser {
  protected $min_time;
  protected $max_time;
  protected $show_size;
  protected $file_name;

  /**
   * @inheritdoc
   */
  function extractInfos() {
    preg_match('/^(?P<timestamp>[0-9]+\.?[0-9]*) "(?P<type>\w+)" "(?P<key>[\w|-]+)" ?"?(?P<infos>.*)"?/', $this->line, $match);
    if (!$match || !isset($match['key'])) {
      return;
    }

    $keys            = explode('-', $match['key']);
    $infos           = ($match['infos']) ? strlen($match['infos']) : 0;
    $this->line_type = CMbString::upper($match['type']);

    if (!$this->min_time || $this->min_time > $match['timestamp']) {
      $this->min_time = $match['timestamp'];
    }

    if (!$this->max_time || $this->max_time < $match['timestamp']) {
      $this->max_time = $match['timestamp'];
    }

    $this->result = $this->getTree($keys, $this->result, $infos);
  }

  /**
   * Transform a log line into an array and add this array to $this->result
   *
   * @param array $keys  Parts of the string to add to the array
   * @param array $tree  Associative array
   * @param int   $infos Infos for set/get
   *
   * @return array
   */
  function getTree($keys, &$tree = array(), $infos = null) {
    if (!$keys) {
      return $tree;
    }

    $key  = reset($keys);
    $keys = array_slice($keys, 1);

    if (!array_key_exists($key, $tree)) {
      $tree[$key] = array(
        'children'       => array(),
        'Total'          => ($this->show_size) ? $infos : 1,
        $this->line_type => ($this->show_size) ? $infos : 1,
      );
    }
    else {
      $tree[$key]['Total'] = ($this->show_size) ? $tree[$key]['Total'] + $infos : $tree[$key]['Total'] + 1;
      if (array_key_exists($this->line_type, $tree[$key])) {
        $tree[$key][$this->line_type] =
          ($this->show_size) ? $tree[$key][$this->line_type] + $infos : $tree[$key][$this->line_type] + 1;
      }
      else {
        $tree[$key][$this->line_type] = ($this->show_size) ? $infos : 1;
      }
    }

    $tree[$key]['children'] += $this->getTree($keys, $tree[$key]['children'], $infos);

    return $tree;
  }

  /**
   * @inheritdoc
   */
  function parseFile($file_name, $remove = false, $show_size = false) {
    $dest_dir = rtrim(CAppUI::conf('root_dir'), '\\/') . '/tmp/logs-redis';

    if (is_dir($dest_dir)) {
      CMbPath::emptyDir($dest_dir);
    }

    $extract         = CMbPath::extract($file_name, $dest_dir);
    $this->show_size = $show_size;

    if (!$extract) {
      parent::parseFile($file_name);
      $this->file_name = $file_name;
      //if ($remove) {
      //  unlink($file_name);
      //}
    }
    else {
      $file_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dest_dir));

      foreach ($file_iterator as $_name => $_directory) {
        if (is_dir($_name) || strpos($_name, '.log') === false) {
          continue;
        }

        parent::parseFile($_name);
      }

      $this->file_name = $dest_dir;
    }

    //CMbPath::emptyDir($dest_dir);
    return $this->result;
  }

  /**
   * @inheritdoc
   */
  function afterParse() {
    $this->recursiveSortResult($this->result);
  }

  /**
   * Sort an array recursivly using the 'Total' field for the sort for each element in 'children'
   *
   * @param array $array Multi-dimensionnal array to sort
   *
   * @return void
   */
  function recursiveSortResult(&$array) {
    CMbArray::pluckSort($array, SORT_DESC, 'Total');

    foreach ($array as &$_values) {
      if (isset($_values['children']) && $_values['children']) {
        CMbArray::pluckSort($_values['children'], SORT_DESC, 'Total');

        $this->recursiveSortResult($_values['children']);
      }
    }
  }

  /**
   * @param string $file_name Path to the file or directory
   * @param string $key       Key to search
   * @param int    $limit     Number of occurences to return
   *
   * @return array
   */
  function searchOccurences($file_name, $key, $limit = 100) {
    $occurences = array();
    if (is_dir($file_name)) {
      $file_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file_name));

      foreach ($file_iterator as $_name => $_directory) {
        if (is_dir($_name) || strpos($_name, '.log') === false) {
          continue;
        }

        $occurences = $this->countOccurences($_name, $key, $occurences, $limit);

        if (count($occurences) >= $limit) {
          break;
        }
      }
    }
    else {
      $occurences = $this->countOccurences($file_name, $key, array(), $limit);
    }

    return $occurences;
  }

  /**
   * @param string $file_name  Path to the file to parse
   * @param string $key        Key to search
   * @param array  $occurences Previous occurences
   * @param int    $limit      Number of elements to return
   *
   * @return array
   */
  function countOccurences($file_name, $key, $occurences = array(), $limit = 100) {
    $fp = fopen($file_name, 'r');
    while ($line = fgets($fp)) {
      if (count($occurences) >= $limit) {
        break;
      }

      preg_match('/^(?P<timestamp>[0-9]+\.?[0-9]*) "(?P<type>\w+)" "(?P<key>[\w|-]+)" ?"?(?P<infos>.*)"?/', $line, $match);
      if (!$match || !isset($match['key'])) {
        continue;
      }

      if ($match['key'] == $key) {
        $line         = explode(' ', $line);
        $occurences[] = array(
          'timestamp' => gmdate("d/m/Y H:i:s", $line[0]),
          'command'   => $line[1],
          'key'       => $line[2],
          'value'     => (isset($line[3])) ? $line[3] : '',
        );
      }
    }

    return $occurences;
  }

  /**
   * @return mixed
   */
  public function getMinTime() {
    return $this->min_time;
  }

  /**
   * @return mixed
   */
  public function getMaxTime() {
    return $this->max_time;
  }

  /**
   * @return string
   */
  function getFileName() {
    return $this->file_name;
  }
}
