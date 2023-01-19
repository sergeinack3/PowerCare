<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkAdmin();

$show_inactive = CView::get("inactive", "bool default|0", true);
$refresh_mode  = CView::get("refresh_mode", "bool default|0");
CView::checkin();

// Liste des Type d'anesthésie
$type_anesth = new CTypeAnesth();
$where = array(
  "actif" =>  ($show_inactive) ? " IN ('0','1')" : " = '1' "
);

/** @var CTypeAnesth[] $types_anesth */
$types_anesth = $type_anesth->loadList($where, "name");
foreach ($types_anesth as &$_type_anesth) {
  $_type_anesth->countOperations();
  $_type_anesth->loadRefGroup();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("types_anesth" , $types_anesth);
$smarty->assign("show_inactive", $show_inactive);
$smarty->assign("refresh_mode" , $refresh_mode);
$smarty->display("vw_edit_typeanesth");