<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Class CPDOMySQLDataSource
 */
class CPDOMySQLDataSource extends CPDODataSource {
  protected $driver_name = "mysql";

  /**
   * @inheritdoc
   */
  function version() {
    return $this->loadResult("SELECT VERSION()");
  }

  /**
   * @inheritdoc
   */
  function ping() {
    $this->link->query("SELECT SQL_NO_CACHE 1");

    return true;
  }

  /**
   * @inheritdoc
   */
  function disableCache() {
    $this->link->query("SET SESSION query_cache_type = OFF;");
  }

  /**
   * @inheritdoc
   */
  function canLimitExecutionTime() {
    $can_limit = false;

    $infos = $this->getVersionInfos();

    if (stripos($infos['engine'], 'mariadb') !== false) {
      /**
       * MariaDB version is 10.X.X-MariaDB
       */
      // max_execution_time is not implemented before 10.1.1
      if (version_compare($infos['version'], "10.1.1") >= 0) {
        $can_limit = true;
      }
    }
    elseif (stripos($infos['engine'], 'mysql') !== false) {
      /**
       * MySQL version can be one of the kind :
       * 5.X.XX-0ubuntu0.XX.XX.X
       * 5.X.XX-X.X+squeeze+build0+1-log
       * 5.X.XX-log
       * 5.X.XX-X.X
       */
      if (version_compare($infos['version'], "5.7.4") >= 0) {
        $can_limit = true;
      }
    }

    return $can_limit;
  }


  /**
   * @inheritdoc
   */
  function limitExecutionTime($query, $max_time) {
    if ($this->canLimitExecutionTime()) {
      $infos = $this->getVersionInfos();

      // MariaDB detection
      if (stripos($infos['engine'], 'mariadb') !== false) {
        // Statement time in second
        $query = "SET STATEMENT MAX_STATEMENT_TIME=$max_time FOR " . $query;
      }
      // For MySQL
      elseif (stripos($infos['engine'], 'mysql') !== false) {
        // Max execution time in ms
        $max_time *= 1000;
        $query    = preg_replace("/SELECT/", "SELECT /*+ MAX_EXECUTION_TIME($max_time) */", $query, 1);
      }
    }

    return $query;
  }
}
