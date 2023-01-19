<?php
/**
 * @package Mediboard\Core\FileUtil
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

/**
 * DIF Files reader
 */
class CDIFFile {
  const table_header   = 'TABLE';
  const vectors_header = 'VECTORS';
  const tuples_header  = 'TUPLES';
  const label_header   = 'LABEL';
  const data_header    = 'DATA';

  public $header_chunks = array('TABLE', 'VECTORS', 'TUPLES', 'LABEL', 'DATA');

  public $handle;

  public $table_name;

  public $column_names;

  public $nb_columns = 0;

  public $nb_entries;

  public $data_pointer;

  public $rows;

  /**
   * Standard constructor
   *
   * @param mixed $handle File handle of file path
   *
   * @return CDIFFile
   */
  function __construct($handle) {
    $this->handle = (is_string($handle)) ? fopen($handle, "r+") : $handle;

    $this->getNbEntries();
    $this->getColumnNames();
  }

  /**
   * Get spreadsheet name
   *
   * @return null|string
   */
  function getTableName() {
    rewind($this->handle);

    $this->table_name = null;
    $header           = $this->readLine();

    if ($header == $this::table_header) {
      // We can ignore next line
      $this->table_name = $this->readNthLine(2);
    }

    return $this->table_name;
  }

  /**
   * Get all columns
   *
   * @return array
   */
  function getColumnNames() {
    rewind($this->handle);

    $this->column_names = array();
    $group              = $this->getAllSpecificGroupLine('LABEL');

    $count_columns = ($this->nb_columns) ? false : true;
    if ($group) {
      foreach ($group as $_group) {
        $_id                         = explode(',', $_group[1]);
        $this->column_names[$_id[0]] = trim(trim($_group[2]), '"');

        if ($count_columns) {
          $this->nb_columns++;
        }
      }
    }

    return $this->column_names;
  }

  /**
   * Count columns
   *
   * @return int
   */
  function getNbColumns() {
    rewind($this->handle);

    $group = $this->getSpecificGroupLine('VECTORS');
    if ($group) {
      $_group           = explode(',', $group[1]);
      $this->nb_columns = (int)$_group[1];
    }

    return $this->nb_columns;
  }

  /**
   * Count vectors
   *
   * @return string
   */
  function getNbEntries() {
    rewind($this->handle);

    $group = $this->getSpecificGroupLine('TUPLES');
    if ($group) {
      $this->nb_entries = $this->readValueFromGroup($group);
    }

    return $this->nb_entries;
  }

  /**
   * <HEADER>
   * -1|0|1,<VALUE>
   * <VALUE>
   *
   * @return string
   */
  function readValue() {
    $header = $this->readLine();

    $value = "";
    if (in_array($header, $this->header_chunks)) {
      // Get first value line
      $header_value = $this->readLine();

      switch (substr($header_value, 0, 1)) {
        // Numeric type, value is second number
        case '0':
          return $value = (int)substr($header_value, 2);

        // String type, value is next line
        case '1':
          return $this->readLine();

        default:
      }
    }

    return $value;
  }

  /**
   * -1|0|1,<VALUE>
   * <VALUE>
   *
   * @return string
   */
  function readBOTValue() {
    $value = "";

    // Get first value line
    $header_value = $this->readLine();

    switch (substr($header_value, 0, 1)) {
      // Numeric type, value is second number
      case '0':
        $value = (int)substr($header_value, 2);
        // Skip next line
        $this->readLine();
        break;

      // String type, value is next line
      case '1':
        $value = $this->readLine();
        break;

      default:
    }

    return $value;
  }

  /**
   * <HEADER>
   * -1|0|1,<VALUE>
   * <VALUE>
   *
   * @return string
   */
  function readValueFromGroup($group) {
    switch (substr($group[1], 0, 1)) {
      // Numeric type, value is second number
      case '0':
        return $value = (int)substr($group[1], 2);

      // String type, value is next line
      case '1':
        return trim(trim($group[2]), '"');

      default:
        return null;
    }
  }

  /**
   * Get the full content of the file
   *
   * @return string
   */
  function getContent() {
    rewind($this->handle);

    $content = "";
    while ($s = fgets($this->handle)) {
      $content .= $s;
    }

    return $content;
  }

  /**
   * Read current line with escaping
   *
   * @return string
   */
  function readLine() {
    return trim(trim(fgets($this->handle)), '"');
  }

  /**
   * Read Nth line from current position
   *
   * @param integer $n Line number
   *
   * @return null|string
   */
  function readNthLine($n) {
    $line = null;

    for ($i = 0; $i < $n; $i++) {
      $line = $this->readLine();
    }

    return $line;
  }

  /**
   * Returns a couple of values:
   * <HEADER>
   * -1|0|1,<VALUE>
   * <VALUE>
   *
   * @param string $line
   *
   * @return array
   */
  function getGroupFromLine($line) {
    return array(
      $line,
      $this->readLine(),
      $this->readLine()
    );
  }

  /**
   * Returns a BOT tuple
   *
   * @param integer $pointer Pointer address
   *
   * @return array
   */
  function getBOTGroup($pointer) {
    if (!$this->nb_columns) {
      $this->getNbColumns();
    }

    fseek($this->handle, $pointer);
    $group = array();
    for ($i = 1; $i <= $this->nb_columns; $i++) {
      $group[] = $this->readBOTValue();
    }

    return $group;
  }

  /**
   * Returns a specific couple from header
   *
   * @param string  $header  Header to start from
   * @param integer $n       Group number
   * @param integer $pointer Pointer address
   *
   * @return array|bool
   */
  function getSpecificGroupLine($header, $n = 1, $pointer = null) {
    ($pointer) ? fseek($this->handle, $pointer) : rewind($this->handle);

    $i = 1;
    while (($line = $this->readLine()) != 'EOD') {
      if ($line == $header) {
        if ($i == $n) {
          return $this->getGroupFromLine($line);
        }
        else {
          $i++;
        }
      }
    }

    return false;
  }

  /**
   * Returns all couples from a specific header
   *
   * @param callable $callback Callback function
   *
   * @return array
   */
  function getAllBOT(callable $callback = null) {
    if (!$this->data_pointer) {
      $this->getDataHeaderPointer();
    }

    $bots = array();
    while (($line = $this->readLine()) != 'EOD') {
      if ($line == 'BOT') {
        if ($callback) {
          $callback($this->getRow($this->getBOTGroup(ftell($this->handle))));
        }
        else {
          $bots[] = $this->getBOTGroup(ftell($this->handle));
        }
      }
    }

    return $bots;
  }

  /**
   * Returns all couples from a specific header
   *
   * @param string  $header  Header to get
   * @param integer $pointer Pointer address
   *
   * @return array
   */
  function getAllSpecificGroupLine($header, $pointer = null) {
    ($pointer) ? fseek($this->handle, $pointer) : rewind($this->handle);

    $group = array();
    while (($line = $this->readLine()) != 'EOD') {
      if ($line == $header) {
        $group[] = $this->getGroupFromLine($line);
      }
    }

    return $group;
  }

  /**
   * Get the pointer address of DATA header
   *
   * @return int
   */
  function getDataHeaderPointer() {
    rewind($this->handle);

    $this->getSpecificGroupLine('DATA');

    return $this->data_pointer = ftell($this->handle);
  }

  /**
   * Get all the rows
   *
   * @param callable|null $callback Callback function
   *
   * @return array
   */
  function getRows($callback = null) {
    if (!$this->data_pointer) {
      $this->getDataHeaderPointer();
    }

    $rows = array();
    $bots = $this->getAllBOT($callback);
    foreach ($bots as $_bot) {
      $rows[] = $this->getRow($_bot);
    }

    return $this->rows = $rows;
  }

  /**
   * Map row into associative array
   *
   * @param array $group Row numeric array
   *
   * @return array
   */
  function getRow($group) {
    $row = array_values($this->column_names);

    return array_combine($row, $group);
  }
}
