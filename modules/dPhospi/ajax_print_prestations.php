<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Hospi\CItemLiaison;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();

$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
$only_souhait = CView::get("only_souhait", "bool");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPraticien();

$model = CCompteRendu::getSpecialModel($sejour->_ref_praticien, $sejour->_class, '[AFFICHAGE PRESTATIONS]');
if ($model->_id) {
  CCompteRendu::streamDocForObject($model, $sejour);
}

$sejour->loadRefPatient();
$sejour->loadPatientBanner();

$dates          = array();
$liaisons       = array();
$liaisons_by_id = array();

$sejour->getIntervallesPrestations($liaisons, $dates, $liaisons_by_id, $only_souhait);
/** @var CItemLiaison $_liaison */
foreach ($liaisons_by_id as $_liaison) {
  $_liaison->loadRefItemRealise();
  $_liaison->loadRefPrestation();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("liaisons", $liaisons_by_id);
$smarty->assign("dates", $dates);
$smarty->assign("only_souhait", $only_souhait);

$smarty->display("inc_print_prestations");