<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\GestionCab\CGestionCab;
use Ox\Mediboard\GestionCab\CModePaiement;
use Ox\Mediboard\GestionCab\CRubrique;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$user = CMediusers::get();
$user->loadRefsFwd();
$user->_ref_function->loadRefsFwd();

$etablissement = $user->_ref_function->_ref_group->text;
$fonction      = $user->_ref_function->text;

$gestioncab_id = CValue::getOrSession("gestioncab_id");

//Recuperation des identifiants pour les filtres
$filter = new CGestionCab();

$filter->_date_min        = CValue::getOrSession("_date_min");
$filter->_date_max        = CValue::getOrSession("_date_max");
$filter->libelle          = CValue::getOrSession("libelle");
$filter->rubrique_id      = CValue::getOrSession("rubrique_id");
$filter->mode_paiement_id = CValue::getOrSession("mode_paiement_id");

$gestioncab = new CGestionCab();
$gestioncab->load($gestioncab_id);

if (!$gestioncab->gestioncab_id) {
  $gestioncab->function_id = $user->function_id;
}

$where = array();
$where["function_id"] = "IS NULL";

// Récupération de la liste des rubriques hors fonction
$listRubriques = new CRubrique();
$listRubriques = $listRubriques->loadList($where);

// Récupération de la liste des mode de paiement hors fonction
$listModesPaiement = new CModePaiement();
$listModesPaiement = $listModesPaiement->loadList($where);

// Récupération de la liste des rubriques liés aux fonctions
if ($user->function_id) {
  $where["function_id"]  = "= '$user->function_id'";
  $listRubriquesFonction = new CRubrique;
  $listRubriquesFonction = $listRubriquesFonction->loadList($where);
  
  // Récupération de la liste des mode de paiement liés aux fonctions
  $listModePaiementFonction = new CModePaiement;
  $listModePaiementFonction = $listModePaiementFonction->loadList($where);
}
else {
  $listRubriquesFonction    = array();
  $listModePaiementFonction = array();
  $where                    = array();
}


$listGestionCab = new CGestionCab();
$where["date"]  = "BETWEEN '$filter->_date_min' AND '$filter->_date_max'";

if ($filter->libelle) {
  $where["libelle"] = "LIKE '%$filter->libelle%'";
}

if ($filter->rubrique_id) {
  $where["rubrique_id"] = "= '$filter->rubrique_id'";
}

if ($filter->mode_paiement_id) {
  $where["mode_paiement_id"] = "= '$filter->mode_paiement_id'";
}

$order          = "date ASC";
$listGestionCab = $listGestionCab->loadList($where, $order);

foreach ($listGestionCab as $key => $fiche) {
  $listGestionCab[$key]->loadRefsFwd();
}

$smarty = new CSmartyDP();

$smarty->assign("etablissement",            $etablissement);
$smarty->assign("fonction",                 $fonction);
$smarty->assign("gestioncab",               $gestioncab);
$smarty->assign("filter",                   $filter);
$smarty->assign("listRubriques",            $listRubriques);
$smarty->assign("listRubriquesFonction",    $listRubriquesFonction);
$smarty->assign("listModesPaiement",        $listModesPaiement);
$smarty->assign("listModePaiementFonction", $listModePaiementFonction);
$smarty->assign("listGestionCab",           $listGestionCab);

$smarty->display("edit_compta");
