<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$page             = CValue::get("page", 0);
$prenom           = trim(CValue::getOrSession("prenom"));
$nom              = trim(CValue::getOrSession("nom"));
$nom_jeune_fille  = trim(CValue::getOrSession("nom_jeune_fille"));
$sexe             = CValue::get("sexe");
//$IPP              = CValue::get("IPP");
//$date_creation    = CValue::get("date_creation");
//$createur         = CValue::get("createur");

$where           = array();
$where[]         = "`nom` LIKE '$nom%' OR `nom_jeune_fille` LIKE '$nom%'";
$where["prenom"] = "LIKE '$prenom%'";
$where["nom"]    = "LIKE '$nom%'";

if ($sexe != "") {
  $where["sexe"] = "= '$sexe'";
}

/*if ($IPP) {
  /*$patient = new CPatient;
  $patient->_IPP = $patient_ipp;
  $patient->loadFromIPP();
}*/

$order = "nom, prenom, naissance";

$step  = 30;
$limit = "$page, $step";

$patient = new CPatient();
$patient->nom             = $nom;
$patient->prenom          = $prenom;
$patient->nom_jeune_fille = $nom_jeune_fille;
$patient->sexe            = $sexe;
//$patient->_IPP            = $IPP;

$nb_pat  = $patient->countList($where);

/** @var CPatient[] $patients CPatient[] */
$patients = $patient->loadList($where, $order, $limit);

CPatient::massLoadIPP($patients);
foreach ($patients as $_patient) {
  $_patient->loadFirstLog()->loadRefUser();
}

$smarty = new CSmartyDP();
$smarty->assign("patient" , $patient);
$smarty->assign("patients", $patients);
$smarty->assign("nb_pat"  , $nb_pat);
$smarty->assign("page"    , $page);
$smarty->display("inc_list_demographic.tpl");