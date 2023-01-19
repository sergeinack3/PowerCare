<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CSQLDataSource;

CCanDo::checkAdmin();

$sourcePath   = "modules/ssr/base/presta_ssr.zip";
$targetDir    = "tmp/ssr";
$targetTables = "tmp/ssr/presta_ssr.sql";

$ds = CSQLDataSource::get("presta_ssr");

// Extract the SQL dump
if (null == $nbFiles = CMbPath::extract($sourcePath, $targetDir)) {
  CAppUI::stepAjax("extraction-error", UI_MSG_ERROR, $sourcePath);
}

CAppUI::stepAjax("extraction-success", UI_MSG_OK, $sourcePath, $nbFiles);

// Création des tables
if (null == $count = $ds->queryDump($targetTables, false)) {
  $msg = $ds->error();
  CAppUI::stepAjax("ssr-import-tables-error", UI_MSG_ERROR, $msg);
}
CAppUI::stepAjax("ssr-import-tables-success", UI_MSG_OK, $count);
