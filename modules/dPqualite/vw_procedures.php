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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Qualite\CChapitreDoc;
use Ox\Mediboard\Qualite\CDocGed;
use Ox\Mediboard\Qualite\CThemeDoc;

CCanDo::checkRead();

$doc_ged_id  = CValue::getOrSession("doc_ged_id");
$theme_id    = CValue::getOrSession("theme_id");
$chapitre_id = CValue::getOrSession("chapitre_id");
$sort_by     = CValue::getOrSession("sort_by", "date");
$sort_way    = CValue::getOrSession("sort_way", "DESC");

$docGed = new CDocGed();
if (!$docGed->load($doc_ged_id)) {
  // Ce document n'est pas valide
  $doc_ged_id = null;
  CValue::setSession("doc_ged_id");
  $docGed = new CDocGed;
}
else {
  $docGed->loadLastActif();
  if (!$docGed->_lastactif->doc_ged_suivi_id || $docGed->annule) {
    // Ce document n'est pas Terminé ou est suspendu
    $doc_ged_id = null;
    CValue::setSession("doc_ged_id");
    $docGed = new CDocGed;
  }
  else {
    $docGed->_lastactif->loadFile();
    $docGed->loadRefs();
  }
}

$group = CGroups::loadCurrent();

// Liste des Thèmes
$theme   = new CThemeDoc();
$where   = array();
$where[] = "group_id = '$group->_id' OR group_id IS NULL";
/** @var CThemeDoc[] $listThemes */
$listThemes = $theme->loadlist($where, "nom");

// Liste des chapitres
$chapitre         = new CChapitreDoc();
$order            = "group_id, nom";
$where            = array();
$where["pere_id"] = "IS NULL";
$where[]          = "group_id = '$group->_id' OR group_id IS NULL";
/** @var CChapitreDoc[] $listChapitres */
$listChapitres = $chapitre->loadlist($where, $order);
foreach ($listChapitres as $_chapitre) {
  $_chapitre->loadChapsDeep();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("theme_id", $theme_id);
$smarty->assign("chapitre_id", $chapitre_id);
$smarty->assign("listThemes", $listThemes);
$smarty->assign("listChapitres", $listChapitres);
$smarty->assign("docGed", $docGed);
$smarty->assign("fileSel", new CFile());
$smarty->assign("sort_by", $sort_by);
$smarty->assign("sort_way", $sort_way);

$smarty->display("vw_procedures.tpl");
