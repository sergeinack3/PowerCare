<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkRead();
$sejour_id = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

// Séjour concerné
$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

if (!$sejour->_id) {
  CAppUI::stepAjax("Séjour inexistant", UI_MSG_ERROR);
}

if ($sejour->type != "ssr") {
  CAppUI::stepAjax("Le séjour sélectionné n'est pas un séjour de type SSR (%s)", UI_MSG_ERROR, $sejour->type);
}

// Chargment du bilan
$bilan = $sejour->loadRefBilanSSR();

// Liste des RHSs du séjour
$_rhs = new CRHS();
$rhss = CRHS::getAllRHSsFor($sejour);
foreach ($rhss as $_rhs) {
  $sejour = $_rhs->loadRefSejour();
  $sejour->loadRefLastRhs();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("bilan", $bilan);
$smarty->assign("rhss", $rhss);

$smarty->display("inc_cotation_rhs");