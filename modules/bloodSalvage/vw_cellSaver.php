<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CCellSaver;

CCanDo::checkRead();
$cell_saver_id = CValue::getOrSession("cell_saver_id");

$cell_saver      = new CCellSaver();
$cell_saver_list = $cell_saver->loadList();
if ($cell_saver_id) {
    $cell_saver = new CCellSaver();
    $cell_saver->load($cell_saver_id);
}

$smarty = new CSmartyDP();

$smarty->assign("cell_saver_list", $cell_saver_list);
$smarty->assign("cell_saver", $cell_saver);

$smarty->display("vw_cellSaver.tpl");
