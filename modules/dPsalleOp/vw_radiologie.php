<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CAmpli;
use Ox\Mediboard\PlanningOp\COperation;

// Récuperation du précédent filtre
$filter   = new COperation();
$filter->_date_min = CView::get("_date_min", "date default|now", true);
$filter->_date_max = CView::get("_date_max", "date default|now", true);
$filter->chir_id   = CView::get("chir_id", "str", true);
$salle_ids  = CView::get("salle_ids", "str", true);
$ampli_ids  = CView::get("ampli_ids", "str", true);

$ccam_codes  = CView::get("ccam_codes", "str", true);

CView::checkin();

if (is_array($salle_ids)) {
    $salle_ids = array_filter($salle_ids);
}
if (is_array($ampli_ids)) {
    $ampli_ids = array_filter($ampli_ids);
}

$ampli = new CAmpli();
$amplis = $ampli->loadGroupList(null, 'libelle');

$blocs = CGroups::loadCurrent()->loadBlocs(PERM_READ);

// Récupération de la liste des praticiens
$prat  = CMediusers::get();
$prats = $prat->loadPraticiens();

$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);
$smarty->assign("salle_ids", is_array($salle_ids) ? $salle_ids : []);
$smarty->assign("ampli_ids", is_array($ampli_ids) ? $ampli_ids : []);
$smarty->assign("ccam_codes", $ccam_codes != '' ? explode('|', $ccam_codes) : []);

$smarty->assign("blocs", $blocs);
$smarty->assign("amplis", $amplis);
$smarty->assign("prats", $prats);

$smarty->display("vw_radiologie");
