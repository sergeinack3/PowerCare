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
 * Description
 */
class CFormattedFileReader extends CFileReader {
  protected $separator = '|';
  protected $header = [];
  protected $sanitize = [];

  protected $internal_counter = 0;

  /**
   * @inheritdoc
   */
  public function __construct($file_path, $start = 0, $skip_headers = true) {
    parent::__construct($file_path, $start);

    if ($skip_headers && $start == 0) {
      $this->readLine(false); // Ignore titles
    }
  }

  /**
   * @inheritdoc
   *
   * @param array $line Line exploded
   */
  protected function sanitizeLine($line) {
    if ($line && is_array($line)) {
      foreach ($this->sanitize as $_callback) {
        $line = array_map($_callback, $line);
      }

      $this->removeEmptyStrings($line);

      return $line;
    }

    return null;
  }

  protected function removeEmptyStrings(&$line) {
    foreach ($line as &$_line) {
      $_line = (empty($_line)) ? null : $_line;
    }
  }

  /**
   * @inheritdoc
   */
  public function readLine($assoc = true) {
    $line = parent::readLine();
    $this->internal_counter++;
    if (!$line) {
      return null;
    }

    $line = explode($this->separator, $line);

    if ($assoc) {
      if (count($line) != count($this->header)) {
        CAppUI::stepAjax("common-error-Arrays do not have the same size", UI_MSG_WARNING);
        return null;
      }

      $line = array_combine($this->header, $line);
    }

    return $line;
  }

  /**
   * Set fields names
   *
   * @param array $names Names to use for the fields of the line
   *
   * @return void
   */
  public function setHeader($names = array()) {
    $this->header = $names;
  }

  /**
   * Get the titles
   *
   * @return array|bool|false|string|null
   */
  public function getTitles() {
    $pos = ftell($this->fp);
    rewind($this->fp);

    $titles = $this->readLine(false);

    fseek($this->fp, $pos);

    return $titles;
  }

  /**
   * @param string $separator Separator to use for the lines
   *
   * @return void
   */
  public function setSeparator($separator) {
    $this->separator = $separator;
  }

  /**
   * Set the callbacks to use to sanitize a line
   *
   * @param array $callbacks Callbacks to call with array_map on the line
   *
   * @return void
   */
  public function setSanitize($callbacks) {
    $this->sanitize = $callbacks;
  }

  /**
   * @inheritdoc
   */
  protected function goToStart($start) {
    for ($i = 0; $i < $start; $i++) {
      fgets($this->fp);
      $this->internal_counter++;
    }
  }

  public function getInternalCounter() {
    return $this->internal_counter;
  }
}