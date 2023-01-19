<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Mediboard\Developpement\CApacheConfiguration;
use Ox\Mediboard\Developpement\CMediboardConfiguration;
use Ox\Mediboard\Developpement\CMySQLConfiguration;
use Ox\Mediboard\Developpement\CPhpConfiguration;

CCanDo::checkRead();

$phpConfigurationInstance = new CPhpConfiguration();
$mysqlConfigurationInstance = new CMySQLConfiguration();
$apacheConfigurationInstance = new CApacheConfiguration();
$mediboardConfigurationInstance = new CMediboardConfiguration();

$mysqlConfigurationInstance->init();
$apacheConfigurationInstance->init();
$phpConfigurationInstance->init();
$mediboardConfigurationInstance->init();
/*$phpConfiguration = $phpConfigurationInstance->serialize();
$mysqlConfiguration = $mysqlConfigurationInstance->serialize();
$apacheConfiguration = $apacheConfigurationInstance->serialize();*/

$returnArray = array(
  "apache" => $apacheConfigurationInstance,
  "php" => $phpConfigurationInstance,
  "mysql" => $mysqlConfigurationInstance,
  "mediboard" => $mediboardConfigurationInstance
);

echo json_encode($returnArray);
