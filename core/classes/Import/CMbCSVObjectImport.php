<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Import;

use Ox\Core\FileUtil\CCSVFile;

/**
 * Description
 */
abstract class CMbCSVObjectImport extends CMbObjectImport {
  /** @var CCSVFile */
  protected $csv;

  protected $profile;

  protected $nb_treated_line;
  protected $current_line = 0;
  protected $nb_errors;

  public static $options = array();

  /**
   * CMbCSVObjectImport constructor.
   *
   * @param string $file_path Path to the CSV file
   * @param int    $start     Start the import at line $start
   * @param int    $step      Import $step lines
   * @param string $profile   Profile tu use to read the CSV
   */
  function __construct($file_path, $start = 0, $step = 100, $profile = CCSVFile::PROFILE_EXCEL) {
    parent::__construct($file_path, $start, $step);
    $this->profile = $profile;
    $this->nb_treated_line = 0;
    $this->nb_errors = 0;
  }

  /**
   * @inheritdoc
   */
  function openFile() {
    parent::openFile();

    if (!$this->fp) {
      $this->csv = null;
    }

    $this->csv = new CCSVFile($this->fp, $this->profile);
  }

  protected function countLines(): int
  {
      return $this->csv->countLines() ?? 0;
  }

  /**
   * Set the columns names
   *
   * @return void
   */
  function setColumnNames() {
    $this->csv->column_names = $this->csv->readLine(true, true);
  }

  /**
   * Set the CSV pointer to the first line to import
   *
   * @return void
   */
  function setPointerToStart() {
    if ($this->start > 1) {
      $this->csv->jumpLine($this->start-1);
    }
  }

  /**
   * Read a line from a CSV and return it sanitized
   *
   * @param bool $assoc                Return line in assoc array
   * @param bool $nullify_enpty_values Replace empty values by null
   *
   * @return array
   */
  function readAndSanitizeLine($assoc = true, $nullify_enpty_values = true) {
    return $this->sanitizeLine($this->csv->readLine($assoc, $nullify_enpty_values));
  }
}
