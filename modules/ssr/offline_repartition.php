<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CColorLibelleSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CBilanSSR;
use Ox\Mediboard\Ssr\CPlateauTechnique;
use Ox\Mediboard\Ssr\CReplacement;

CCanDo::checkRead();

CApp::setMemoryLimit("768M");

global $m;
$date = CView::get("date", "date default|now");
CView::enforceSlave();
CView::checkin();

// Chargement des plateaux disponibles
/** @var CSejour[][] $sejours */
$sejours = array();
/** @var CReplacement[][] $replacements */
$replacements = array();
/** @var CSejour[] $all_sejours */
$all_sejours = array();

$where    = array();
$where[]  = "type = '$m' OR type IS NULL";
$plateau  = new CPlateauTechnique();
$plateaux = $plateau->loadGroupList($where);

/** @var CPlateauTechnique[] $plateaux */
foreach ($plateaux as $_plateau) {
  foreach ($_plateau->loadRefsTechniciens() as $_technicien) {
    $_technicien->loadRefCongeDate($date);

    $_technicien->loadRefKine();
    $kine_id = $_technicien->_ref_kine->_id;

    // Chargement des sejours du technicien
    $sejours[$_technicien->_id] = CBilanSSR::loadSejoursSSRfor($_technicien->_id, $date);

    /** @var CSejour $_sejour */
    foreach ($sejours[$_technicien->_id] as $_sejour) {
      $_sejour->checkDaysRelative($date);
      $_sejour->loadRefPatient(1);
      $_sejour->loadRefBilanSSR();
      $all_sejours[] = $_sejour;
    }

    // Chargement de ses remplacements
    $replacement                     = new CReplacement;
    $replacements[$_technicien->_id] = $replacement->loadListFor($kine_id, $date);

    /** @var CReplacement $_replacement */
    foreach ($replacements[$_technicien->_id] as $_replacement) {
      // Détail sur le congé
      $_replacement->loadRefConge();
      $_replacement->_ref_conge->loadRefUser();
      $_replacement->_ref_conge->_ref_user->loadRefFunction();

      // Détails des séjours remplacés
      $_replacement->loadRefSejour();
      $sejour =& $_replacement->_ref_sejour;
      $sejour->checkDaysRelative($date);
      $sejour->loadRefPatient(1);
      $sejour->loadRefBilanSSR();

      $all_sejours[] = $sejour;
    }
  }
}

// Couleurs
$colors = CColorLibelleSejour::loadAllFor(CMbArray::pluck($all_sejours, "libelle"));

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("date", $date);
$smarty->assign("plateaux", $plateaux);
$smarty->assign("sejours", $sejours);
$smarty->assign("colors", $colors);
$smarty->assign("replacements", $replacements);
$smarty->display("offline_repartition");
