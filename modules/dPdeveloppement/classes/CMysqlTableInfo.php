<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use DateTimeImmutable;
use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Hold data about tables information on the information_schema database
 */
class CMysqlTableInfo implements IShortNameAutoloadable {
  public $tableCatalog = "";
  public $tableSchema = "";
  public $tableName = "";
  public $tableType = "";
  public $tableEngine = "";
  public $tableVersion = 0;
  public $tableRowFormat = "";
  public $tableRowsCount = 0;
  public $tableAvgRowLength = 0;
  public $tableDataLength = 0;
  public $tableMaxDataLength = 0;
  public $tableIndexLength = 0;
  public $tableDataFree = 0;
  public $tableAutoIncrement = 0;
  public $tableCreateTime = null;
  public $tableUpdateTime = null;
  public $tableCheckTime = null;
  public $tableCollation = "";

  /**
   * Initialize a new CMysqlTableInfo with a given rowList
   *
   * @param array $rowList Associative array that stores the Mysql result
   *
   * @return void
   */
  public function init($rowList) {
    $this->tableCatalog         = $rowList["TABLE_CATALOG"];
    $this->tableSchema          = $rowList["TABLE_SCHEMA"];
    $this->tableName            = $rowList["TABLE_NAME"];
    $this->tableType            = $rowList["TABLE_TYPE"];
    $this->tableEngine          = $rowList["ENGINE"];
    $this->tableVersion         = $rowList["VERSION"];
    $this->tableRowFormat       = $rowList["ROW_FORMAT"];
    $this->tableRowsCount       = $rowList["TABLE_ROWS"];
    $this->tableAvgRowLength    = $rowList["AVG_ROW_LENGTH"];
    $this->tableDataLength      = $rowList["DATA_LENGTH"];
    $this->tableMaxDataLength   = $rowList["MAX_DATA_LENGTH"];
    $this->tableIndexLength     = $rowList["INDEX_LENGTH"];
    $this->tableDataFree        = $rowList["DATA_FREE"];
    $this->tableAutoIncrement   = $rowList["AUTO_INCREMENT"];
    $this->tableCreateTime      = new DateTimeImmutable($rowList["CREATE_TIME"]);
    $this->tableUpdateTime      = new DateTimeImmutable($rowList["UPDATE_TIME"]);
    $this->tableCheckTime       = new DateTimeImmutable($rowList["CHECK_TIME"]);
    $this->tableCollation       = $rowList["TABLE_COLLATION"];
  }
}