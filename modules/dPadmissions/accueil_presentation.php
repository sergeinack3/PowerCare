<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour = new CSejour();
$_statut_pec      = CView::get("_statut_pec", "str", true);
$praticien_id     = CView::get("praticien_id", "num", true);
$type_pec         = CView::get("type_pec", "str default|".$sejour->_specs["type_pec"]->list);
$enabled_services = CView::get("active_filter_services", "bool default|0", true);
$period           = CView::get("period", "str", true);

CView::checkin();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("_statut_pec"     , $_statut_pec);
$smarty->assign("praticien_id"    , $praticien_id);
$smarty->assign("type_pec"        , json_encode($type_pec));
$smarty->assign("enabled_services", $enabled_services);
$smarty->assign("period"          , $period);

$smarty->display("accueil_presentation");

