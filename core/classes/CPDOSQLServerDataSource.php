<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * MySQL vs. SQLServer
 * http://technet.microsoft.com/en-us/library/cc966396.aspx
 * http://www.codeproject.com/KB/database/migrate-mysql-to-mssql.aspx
 */

/**
 * Class CPDOSQLServerDataSource
 */
class CPDOSQLServerDataSource extends CPDODataSource {
  protected $driver_name = "sqlserv";
}
