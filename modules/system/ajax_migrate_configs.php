<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbConfig;
use Ox\Core\CSQLDataSource;

CCanDo::checkAdmin();

global $dPconfig;

$configs = $dPconfig;

foreach ($configs as $_key => $_config) {
  if (in_array($_key, CMbConfig::$forbidden_values) || in_array($_key, array("db", "php"))) {
    unset($configs[$_key]);
  }
}

$inserts = array();
$ds = CSQLDataSource::get("std");

$list = array();
CMbConfig::buildConf($list, $configs, null);

$count_configs = count($list);
$count = 0;

foreach ($list as $key => $value) {
  $query = "INSERT INTO `config_db`
      VALUES (%1, %2)
      ON DUPLICATE KEY UPDATE value = %3";
  $query = $ds->prepare($query, $key, $value, $value);

  if ($ds->exec($query) === false) {
    CAppUI::stepAjax("Configure-failed-modify", UI_MSG_ERROR);
  }
  else {
    $count++;
  }
}

if ($count_configs == $count) {
  CAppUI::stepAjax("Toutes les configurations ont été transférées");
}
else {
  CAppUI::stepAjax("$count / $count_configs configurations transférées", UI_MSG_WARNING);
}