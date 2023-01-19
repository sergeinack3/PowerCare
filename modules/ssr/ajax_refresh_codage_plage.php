<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CPlageSeanceCollective;

CCanDo::checkRead();
$plage_id                = CView::get("plage_id", "ref class|CPlageSeanceCollective");
$element_prescription_id = CView::get("element_prescription_id", "ref class|CElementPrescription");
CView::checkin();

$plage = new CPlageSeanceCollective();
$plage->load($plage_id);
$plage->element_prescription_id = $element_prescription_id;
$plage->loadRefElementPrescription()->loadRefsCodesSSR();
$plage->loadRefsActes();
$plage->rangeActesOther();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("plage", $plage);
$smarty->display("vw_codage_actes_plage");
