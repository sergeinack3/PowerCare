<?php
/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * @deprecated
 * Generate system user SQL queries
 */
$queries = array();
foreach ($dbConfigs as $dbConfigName => $dbConfig) {
  $host = $dbConfig["dbhost"];
  $name = $dbConfig["dbname"];
  $user = $dbConfig["dbuser"];
  $pass = $dbConfig["dbpass"];
  
  // Create database
  $queries[] = "CREATE DATABASE `$name` ;";

  // Create user with global permissions
  $queries[] = 
   "GRANT USAGE".($dbConfigName == "std" ? ", RELOAD " : "")."
    ON * . * 
    TO '$user'@'$host'
    IDENTIFIED BY '$pass';";
      
  // Grant user with database permissions
  $queries[] = 
   "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES
    ON `$name` . *
    TO '$user'@'$host';";
}

