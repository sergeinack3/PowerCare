<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

CCanDo::checkAdmin();

CView::enforceSlave();

$ds = CSQLDataSource::get("std");

$query = new CRequest();
$query->addSelect(array("author_id", "COUNT(*) as count"));
$query->addTable("files_mediboard");
$query->addWhere(
  array(
    "file_name" => $ds->prepareLike("Dossier complet%"),
  )
);
$query->addGroup("author_id");

$count_to_purge = $ds->loadList($query->makeSelect());

$smarty = new CSmartyDP();
$smarty->assign("purge", $count_to_purge);
$smarty->display('vw_purge_dossiers_export');
