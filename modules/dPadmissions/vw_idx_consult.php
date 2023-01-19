<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

//Initialisations des variables
$date         = CValue::getOrSession("date", CMbDT::date());
$today        = CMbDT::date();
$hour         = CMbDT::time(null);
$board        = CValue::get("board", 1);
$boardItem    = CValue::get("boardItem", 1);
$consult      = new CConsultation;


// Récupération des fonctions
$cabinets = CMediusers::loadFonctions();

// Récupération de la liste des anesthésistes
$mediuser = new CMediusers;
$anesthesistes = $mediuser->loadAnesthesistes(PERM_READ);


if ($consult->consultation_id) {
  $date = $consult->_ref_plageconsult->date;
  CValue::setSession("date", $date);
}

// Récupération des plages de consultation du jour et chargement des références
$listPlages = array();
foreach ($anesthesistes as $anesth) {
  $listPlages[$anesth->_id]["anesthesiste"] = $anesth;
  $plage = new CPlageconsult();
  $where = array();
  $where["chir_id"] = "= '$anesth->_id'";
  $where["date"] = "= '$date'";
  $order = "debut";
  $listPlage = $plage->loadList($where, $order);
  if (count($listPlage)) {
    $listPlages[$anesth->_id]["plages"] = $listPlage;
  }
  else {
    unset($listPlages[$anesth->_id]);
    unset($anesthesistes[$anesth->_id]);
  }
}

foreach ($listPlages as &$element) {
  foreach ($element["plages"] as &$plage) {
    /** @var  CPlageconsult $plage */
    $plage->_ref_chir =& $element["anesthesiste"];
    $plage->loadRefsBack();
    foreach ($plage->_ref_consultations as $keyConsult => &$consultation) {
      if (($consultation->chrono == CConsultation::TERMINE)) {
        unset($plage->_ref_consultations[$keyConsult]);
        continue;
      }
      $consultation->loadRefSejour();
      $consultation->loadRefPatient();
      $consultation->loadRefCategorie();
      $consultation->countDocItems();
    }
  }
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult"      , $consult);
$smarty->assign("listPlages"   , $listPlages);
$smarty->assign("date"         , $date);
$smarty->assign("today"        , $today);
$smarty->assign("hour"         , $hour);
$smarty->assign("anesthesistes", $anesthesistes);
$smarty->assign("cabinets"     , $cabinets);
$smarty->assign("board"        , $board);
$smarty->assign("boardItem"    , $boardItem);
$smarty->assign("canCabinet"   , CModule::getCanDo("dPcabinet"));

$smarty->display("vw_idx_consult.tpl");
