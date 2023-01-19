<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CDepistageGrossesse;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkEdit();

$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefGroup();
$grossesse->getDateAccouchement();
$grossesse->loadRefsNaissances();

$depistage_customs       = array();
$depistage_field_customs = array();

/** @var CDepistageGrossesse[] $depistages */
$depistages = $grossesse->loadBackRefs("depistages", "date ASC");
foreach ($depistages as $depistage) {
  $depistage->getSA();
  $depistage_customs = $depistage->loadRefsDepistageGrossesseCustom();

  foreach ($depistage_customs as $_depistage_custom) {
    if (!isset($depistage_field_customs[$_depistage_custom->libelle])) {
      foreach ($depistages as $_depistage) {
        $depistage_field_customs[$_depistage_custom->libelle][$_depistage->_id] = null;
      }
    }
    $depistage_field_customs[$_depistage_custom->libelle][$depistage->_id] = $_depistage_custom->valeur;
  }
}

ksort($depistage_field_customs);

$patient = $grossesse->loadRefParturiente();
$patient->loadIPP($grossesse->group_id);
$patient->loadRefDossierMedical();

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->assign("depistage_field_customs", $depistage_field_customs);
$smarty->display("dossier_mater_depistages.tpl");

