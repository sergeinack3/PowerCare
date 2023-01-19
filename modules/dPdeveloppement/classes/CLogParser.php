<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;

/**
 * Generic log parser
 */
abstract class CLogParser implements IShortNameAutoloadable {
  protected $result = array();
  protected $fp;
  protected $line;
  protected $line_idx;
  protected $line_type;
  protected $nb_lines = 0;

  public static $log_types = array(
    'redis',
  );

  /**
   *  Parse a log file and return an array
   *
   * @param string $file_name Path to the file to parse
   *
   * @return array
   */
  function parseFile($file_name) {
    $this->init($file_name);

    while ($this->line = fgets($this->fp)) {
      $this->nb_lines++;
      $this->line_idx = ftell($this->fp);
      $this->extractInfos();
    }

    $this->afterParse();

    fclose($this->fp);

    return $this->result;
  }

  /**
   * @param string $file_name Name of the file to open
   *
   * @return void
   */
  function init($file_name) {
    if (!is_file($file_name)) {
      CAppUI::stepAjax('CFile-not-exists', UI_MSG_ERROR, $file_name);
    }

    $this->fp = fopen($file_name, 'r');
    if (!$this->fp) {
      CAppUI::stepAjax('CFile-not-exists', UI_MSG_ERROR, $file_name);
    }

    $this->line_idx = 0;
    if (!$this->result) {
      $this->result   = array();
    }
  }

  /**
   * Extract infos from a log line
   *
   * @return void
   */
  abstract function extractInfos();

  /**
   * Function called after the parsing
   *
   * @return void
   */
  abstract function afterParse();

  /**
   * @return int
   */
  function getNbLines() {
    return $this->nb_lines;
  }
}
