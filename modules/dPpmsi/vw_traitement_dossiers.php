<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$date           = CView::get("date", "date default|now", true);
$type           = CView::get("type", "enum list|ambucomp|ambucompssr|comp|ambu|exte|seances|ssr|psy|urg|consult default|ambu");
$service_id     = CView::get("service_id", "ref class|CService");
$prat_id        = CView::get("prat_id", "ref class|CMediusers");
$order_way      = CView::get("order_way", "enum list|ASC|DESC default|ASC");
$order_col      = CView::get("order_col", "str default|patient_id");
$tri_recept     = CView::get("tri_recept", "str");
$tri_complet    = CView::get("tri_complet", "str");
$period         = CView::get("period", "str");
$filterFunction = CView::get("filterFunction", "str");
CView::checkin();

$sejour = new CSejour();
$sejour->_type_admission = $type;
$sejour->service_id      = explode(",", $service_id);
$sejour->praticien_id    = $prat_id;

// Récupération de la liste des services
$where = array();
$where["externe"]   = "= '0'";
$where["cancelled"] = "= '0'";
$service = new CService();
$services = $service->loadGroupList($where);

// Récupération de la liste des praticiens
$prat = CMediusers::get();
$prats = $prat->loadPraticiens();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour"        , $sejour);
$smarty->assign("services"      , $services);
$smarty->assign("prats"         , $prats);
$smarty->assign("order_way"     , $order_way);
$smarty->assign("order_col"     , $order_col);
$smarty->assign("tri_recept"    , $tri_recept);
$smarty->assign("tri_complet"   , $tri_complet);
$smarty->assign("date"          , $date);
$smarty->assign("period"        , $period);
$smarty->assign("filterFunction", $filterFunction);
$smarty->display("traitement_dossiers/vw_traitement_dossiers");