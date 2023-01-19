<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPersonnel;

CCanDo::checkRead();
$plageop_id = CView::get("plageop_id", "ref class|CPlageOp", true);
$date       = CView::get("date", "date default|now", true);
$bloc_id    = CView::get("bloc_id", "ref class|CBlocOperatoire");
CView::checkin();

// Informations sur la plage demandée
$plagesel = new CPlageOp;
$plagesel->load($plageop_id);
$plagesel->loadRefSalle();

if ($plagesel->_id) {
  $count = $plagesel->countDuplicatedPlages();
}

$listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_READ, null, "nom");

//curent bloc if $bloc_id
$bloc = new CBlocOperatoire();
$bloc->load($bloc_id);
$listSalles = $bloc->loadRefsSalles();
$arrKeySalle = array_keys($listSalles);

// cleanup listBlocs
foreach ($listBlocs as $key => $curr_bloc) {
  $salles = $curr_bloc->loadRefsSalles();
  foreach ($salles as $id => $_salle) {
    if (count($arrKeySalle) && !in_array($id, $arrKeySalle)) {
      unset($salles[$id]);
      continue;
    }
  }

  if (!count($salles)) {
    unset($listBlocs[$key]);
    continue;
  }
}

if (!$plagesel->temps_inter_op) {
  $plagesel->temps_inter_op = "00:00:00";
}
if (!$plagesel->pause) {
  $plagesel->pause = "00:00:00";
}

if ($plagesel->_id) {
  if ((count($arrKeySalle) && !in_array($plagesel->salle_id, $arrKeySalle)) || $plagesel->date != $date) {
    $plageop_id = 0;
    $plagesel = new CPlageOp;
  }
  $plagesel->loadAffectationsPersonnel();
}

if (!$plagesel->_id) {
  $plagesel->date = $date;
  $plagesel->debut = $plagesel->debut_reference = CPlageOp::$hours_start.":00:00";
  $plagesel->fin   = $plagesel->fin_reference   = CPlageOp::$hours_start.":00:00";
}

// On charge le praticien et ses fonctions secondaires
$chir = $plagesel->loadRefChir();
$chir->loadRefFunction();
$_functions = $chir->loadBackRefs("secondary_functions");

// Liste des Specialités
$function = new CFunctions();
$specs = $function->loadSpecialites(PERM_READ, 1);

// Liste des Anesthésistes
$mediuser = new CMediusers();
$anesths = $mediuser->loadAnesthesistes();
CMbObject::massLoadFwdRef($anesths, "function_id");
foreach ($anesths as $_anesth) {
  $_anesth->loadRefFunction();
}

// Liste des praticiens
$chirs = $mediuser->loadChirurgiens();
CMbObject::massLoadFwdRef($chirs, "function_id");
foreach ($chirs as $_chir) {
  $_chir->loadRefFunction();
}

// Chargement du personnel
$listPers = array(
  "iade"              => CPersonnel::loadListPers("iade"),
  "op"                => CPersonnel::loadListPers("op"),
  "op_panseuse"       => CPersonnel::loadListPers("op_panseuse"),
  "sagefemme"         => CPersonnel::loadListPers("sagefemme"),
  "manipulateur"      => CPersonnel::loadListPers("manipulateur"),
  "aux_puericulture"  => CPersonnel::loadListPers("aux_puericulture"),
  "instrumentiste"    => CPersonnel::loadListPers("instrumentiste")
);

if ($plagesel->_id) {
  $plagesel->multicountOperations();
  $plagesel->countOperationsAnnulees();
  $plagesel->loadRefsNotes();
  $plagesel->countOperations();
  $listPers = $plagesel->loadPersonnelDisponible($listPers);
}

//Création du template
$smarty = new CSmartyDP();
$smarty->assign("listBlocs" , $listBlocs);
$smarty->assign("bloc"      , $bloc);
$smarty->assign("date"      , $date);
$smarty->assign("plagesel"  , $plagesel);
$smarty->assign("specs"     , $specs);
$smarty->assign("anesths"   , $anesths);
$smarty->assign("chirs"     , $chirs);
$smarty->assign("listPers"  , $listPers);
$smarty->assign("_functions", $_functions);
$smarty->display("inc_edit_planning");
