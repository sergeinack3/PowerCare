<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CSQLDataSource;
use Ox\Core\FileUtil\CCSVFile;

/**
 * CSV import tools
 */
class CCSVImport {
  /** @var string File path */
  public $csv_path;

  /** @var CCSVFile */
  public $csv;

  /** @var CSQLDataSource */
  public $ds;

  /** @var string Table name */
  public $table_name;

  public $skip_first_line;

  /** @var integer Import step */
  public $step;

  /** @var integer Rows imported */
  public $inserted = 0;

  /** @var integer File resource internal pointer */
  public $pointer;

  /** @var callable Callback on each chunk (n rows) */
  public $chunk_callback;

  /** @var int Chunk size to call the callback on */
  public $chunk_callback_size = 100;

  /** @var int Current line number */
  public $current_line_number = 0;

  /**
   * CCSVImport constructor.
   *
   * @param string $file_path Path to the file to read
   */
  function __construct($file_path) {
    $this->csv_path = $file_path;

    $this->csv = new CCSVFile($this->csv_path, CCSVFile::PROFILE_AUTO);
  }

  /**
   * Import CSV file into SQL table
   *
   * @param string $ds_name         Datasource name
   * @param string $table_name      Table name
   * @param bool   $skip_first_line Skip first line (columns name)
   * @param int    $step            Import step
   *
   * @return int
   */
  function importTable($ds_name, $table_name, $skip_first_line = false, $step = 100) {
    if (!$ds_name) {
      CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, 'DATASOURCE NAME');
    }

    if (!$table_name) {
      CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, 'TABLE NAME');
    }

    $this->ds              = CSQLDataSource::get($ds_name);
    $this->table_name      = $table_name;
    $this->skip_first_line = $skip_first_line;
    $this->step            = ($step < 1) ? 100 : $step;

    ini_set('auto_detect_line_endings', true);

    if ($this->skip_first_line) {
      $this->current_line_number++;
      $this->csv->setColumnNames($this->csv->readLine());
    }

    CSQLDataSource::$log = false;

    do {
      try {
        $this->importTableRows();
      }
      catch (CMbException $e) {
        CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
      }
    } while ($this->pointer);

    CSQLDataSource::$log = true;

    return $this->inserted;
  }

  /**
   * Imports a part of CSV file into SQL table
   *
   * @throws CMbException
   *
   * @return int
   */
  private function importTableRows() {
    if ($this->pointer > 0) {
      fseek($this->csv->handle, $this->pointer);
    }

    $i    = 1;
    $data = array();

    while ($row = $this->csv->readLine(true)) {
      $this->current_line_number++;

      $data[] = $row;

      if ($this->chunk_callback && is_callable($this->chunk_callback)
          && ($this->current_line_number % $this->chunk_callback_size) === 0
      ) {
        call_user_func($this->chunk_callback, $this->current_line_number);
      }

      if ($i == $this->step) {
        break;
      }

      $i++;
    }

    if (!$data) {
      if ($this->chunk_callback && is_callable($this->chunk_callback)) {
        call_user_func($this->chunk_callback, $this->current_line_number);
      }

      return $this->pointer = 0;
    }

    $this->ds->insertMulti($this->table_name, $data, $this->step);

    $this->pointer = ftell($this->csv->handle);

    $this->inserted += count($data);

    return $this->pointer;
  }
}
