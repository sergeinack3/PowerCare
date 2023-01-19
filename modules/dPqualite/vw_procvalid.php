<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Qualite\CCategorieDoc;
use Ox\Mediboard\Qualite\CChapitreDoc;
use Ox\Mediboard\Qualite\CDocGed;
use Ox\Mediboard\Qualite\CThemeDoc;

CCanDo::checkAdmin();

$doc_ged_id        = CValue::getOrSession("doc_ged_id", 0);
$procAnnuleVisible = CValue::getOrSession("procAnnuleVisible", 0);
$lastactif         = CValue::get("lastactif", 0);

$docGed         = new CDocGed();
$listCategories = array();
$listThemes     = array();
$listChapitres  = array();
if (!$docGed->load($doc_ged_id) || $docGed->etat == 0) {
  // Ce document n'est pas valide
  $doc_ged_id = null;
  CValue::setSession("doc_ged_id");
  $docGed = new CDocGed();
}
else {
  $docGed->loadLastActif();
  $docGed->loadRefs();

  // Liste des Catégories
  $categorie      = new CCategorieDoc();
  $listCategories = $categorie->loadlist(null, "code");

  // Liste des Thèmes
  $theme = new CThemeDoc();
  $where = array();
  if ($docGed->group_id) {
    $where [] = "group_id = '$docGed->group_id' OR group_id IS NULL";
  }
  else {
    $where ["group_id"] = "IS NULL";
  }
  $listThemes = $theme->loadlist($where, "group_id, nom");

  // Liste des Chapitres
  $chapitre          = new CChapitreDoc();
  $where             = array();
  $where ["pere_id"] = "IS NULL";
  if ($docGed->group_id) {
    $where [] = "group_id = '$docGed->group_id' OR group_id IS NULL";
  }
  else {
    $where ["group_id"] = "IS NULL";
  }
  /** @var CChapitreDoc[] $listChapitres */
  $listChapitres = $chapitre->loadlist($where, "group_id, code");
  foreach ($listChapitres as &$_chapitre) {
    $_chapitre->loadChapsDeep();
  }
}

$docGed->loadLastEntry();

// Procédure en Cours de demande
$procDemande = CDocGed::loadProcDemande();
foreach ($procDemande as $keyProc => $currProc) {
  $procDemande[$keyProc]->loadRefs();
  $procDemande[$keyProc]->getEtatRedac();
  $procDemande[$keyProc]->loadLastActif();
  $procDemande[$keyProc]->loadLastEntry();
}

// Procédure non terminé Hors demande
$procEnCours = CDocGed::loadProcRedacAndValid();
foreach ($procEnCours as $_proc) {
  $_proc->loadRefs();
  $_proc->getEtatValid();
  $_proc->loadLastEntry();
}

// Procédures Terminée et Annulée
$where           = array();
$where["annule"] = "= '1'";
/** @var CDocGed[] $procTermine */
$procTermine = $docGed->loadList($where);
if ($procAnnuleVisible) {
  foreach ($procTermine as $_proc) {
    $_proc->loadRefs();
    $_proc->getEtatValid();
    $_proc->loadLastEntry();
  }
}

$versionDoc = array();
if ($docGed->version) {
  $versionDoc[] = ($docGed->version) + 0.1;
  $versionDoc[] = intval($docGed->version) + 1;
}
else {
  $versionDoc[] = "1";
}
// Création du template
$smarty = new CSmartyDP();

$smarty->assign("lastactif", $lastactif);
$smarty->assign("procAnnuleVisible", $procAnnuleVisible);
$smarty->assign("procTermine", $procTermine);
$smarty->assign("procDemande", $procDemande);
$smarty->assign("procEnCours", $procEnCours);
$smarty->assign("listCategories", $listCategories);
$smarty->assign("listThemes", $listThemes);
$smarty->assign("listChapitres", $listChapitres);
$smarty->assign("docGed", $docGed);
$smarty->assign("versionDoc", $versionDoc);

$smarty->display("vw_procvalid.tpl");

