<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\BloodSalvage\CTypeEi;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Qualite\CEiCategorie;
use Ox\Mediboard\Qualite\CFicheEi;

CCanDo::checkRead();

$fiche_ei_id = CValue::get("fiche_ei_id", 0);

//Récupération du type de fiche à générer et de la RSPO concernée.
$type_ei_id       = CValue::get("type_ei_id");
$blood_salvage_id = CValue::get("blood_salvage_id");

$fiche   = new CFicheEi();
$listFct = new CFunctions();

// Droit admin et edition de fiche
if (CCanDo::admin() && $fiche_ei_id) {
  $fiche->load($fiche_ei_id);
}

// Chargement des Utilisateurs
if (CCanDo::admin()) {
  $listFct = CMediusers::loadFonctions(PERM_READ);
  foreach ($listFct as $fct) {
    $fct->loadRefsUsers();
  }
}

$fiche->loadRefsFwd();
if (!$fiche->_ref_evenement) {
  $fiche->_ref_evenement = array();
}

/*
 * Si l'on est dans le cas où nous souhaitons préremplir automatiquement 
 * quelques champs à l'aide du modèle de fiche d'incident (module cell saver).
 */
if ($type_ei_id) {
  $type_fiche = new CTypeEi();
  $type_fiche->load($type_ei_id);
  $fiche->elem_concerne  = $type_fiche->concerne;
  $fiche->descr_faits    = $type_fiche->desc;
  $fiche->evenements     = $type_fiche->evenements;
  $fiche->type_incident  = $type_fiche->type_signalement;
  $fiche->_ref_evenement = $type_fiche->_ref_evenement;

  if ($blood_salvage_id) {
    $blood_salvage = new CBloodSalvage();
    $blood_salvage->load($blood_salvage_id);
    $blood_salvage->loadRefsFwd();

    if ($fiche->elem_concerne == "pat") {
      $fiche->elem_concerne_detail = $blood_salvage->_ref_patient->_view;
    }
    if ($fiche->elem_concerne == "mat") {
      $fiche->elem_concerne_detail = $blood_salvage->_ref_cell_saver->_view;
    }
  }
}

// Liste des Catégories
$firstdiv = null;

$categorie = new CEiCategorie();
/** @var CEiCategorie[] $listCategories */
$listCategories = $categorie->loadList(null, "nom");
foreach ($listCategories as $key => $_categorie) {
  if ($firstdiv === null) {
    $firstdiv = $key;
  }
  $_categorie->loadRefsBack();
  $_categorie->_checked = null;
  foreach ($_categorie->_ref_items as $keyItem => $_item) {
    if (in_array($keyItem, $fiche->_ref_evenement)) {
      $_item->_checked = true;
      if ($_categorie->_checked) {
        $_categorie->_checked .= "|$keyItem";
      }
      else {
        $_categorie->_checked = $keyItem;
      }
    }
    else {
      $_item->_checked = false;
    }
  }
}

if (!$fiche->date_incident) {
  $fiche->date_incident = CMbDT::dateTime();
}
$fiche->updateFormFields();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("fiche", $fiche);
$smarty->assign("firstdiv", $firstdiv);
$smarty->assign("listCategories", $listCategories);
$smarty->assign("listFct", $listFct);

$smarty->display("vw_incident.tpl");
