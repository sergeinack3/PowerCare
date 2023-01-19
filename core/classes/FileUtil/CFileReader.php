<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

use Ox\Core\CAppUI;

/**
 * File reader that can handle big files
 */
class CFileReader {
  protected $fp;

  /**
   * CFileReader constructor.
   *
   * @param string $file_path File to read
   * @param int    $start     Number of bytes to skip
   */
  public function __construct($file_path, $start = 0) {
    if (!is_file($file_path)) {
      CAppUI::stepAjax("common-error-File %s does not exists", UI_MSG_ERROR, $file_path);
    }

    $this->fp = fopen($file_path, "r");
    if (!$this->fp) {
      CAppUI::stepAjax("common-error-Can not open file %s", UI_MSG_ERROR, $file_path);
    }

    $this->goToStart($start);
  }

  /**
   * @param int $start
   *
   * @return void
   */
  protected function goToStart($start) {
    fseek($this->fp, $start);
  }

  /**
   * Read a single line from a file
   *
   * @return bool|string
   */
  public function readLine($assoc = true) {
    return fgets($this->fp);
  }

  /**
   * Remove spaces at the start and at the and of a line
   *
   * @param string $line Line to sanitize
   *
   * @return mixed
   */
  protected function sanitizeLine($line) {
    return trim($line);
  }

  /**
   * Read a line then sanitize it
   *
   * @return mixed
   */
  public function readAndSanitizeLine($assoc = true) {
    $line = $this->readLine($assoc);
    return $this->sanitizeLine($line);
  }

  /**
   * Count the lines in a file
   *
   * @return int
   */
  public function countLines() {
    rewind($this->fp);
    $count = 0;
    while (fgets($this->fp)) {
      $count++;
    }

    return $count;
  }

  /**
   * Get the current position in a file
   *
   * @return bool|int
   */
  public function getPos() {
    return ftell($this->fp);
  }

  /**
   * Close the file pointer
   *
   * @return void
   */
  public function close() {
    fclose($this->fp);
  }
}
