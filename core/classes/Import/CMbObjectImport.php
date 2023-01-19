<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Import;

// TODO Remove and use CFileParser ?
/**
 * Description
 */
class CMbObjectImport {
  protected $file_path;
  protected $start;
  protected $step;

  /** @var resource */
  protected $fp;

  /**
   * CMbObjectImport constructor.
   *
   * @param string $file_path Path to the import file
   * @param int    $start     Start the import at line $start
   * @param int    $step      Import $step lines
   */
  function __construct($file_path, $start = 0, $step = 100) {
    $this->file_path = $file_path;
    $this->start = $start;
    $this->step = $step;
  }

  /**
   *  Open a file
   *
   * @return void
   */
  function openFile() {
    $this->fp = fopen($this->file_path, 'r');
  }

  /**
   * Sanitize a line from the file
   *
   * @param array|string $line Line to sanitize
   *
   * @return array|string
   */
  function sanitizeLine($line) {
    return $line;
  }

  /**
   * @return mixed
   */
  function import(){}
}
