<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $can, $g;

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Qualite\CChapitreDoc;
use Ox\Mediboard\Qualite\CThemeDoc;

$can->needsAdmin();

$typeVue       = CValue::getOrSession("typeVue", 0);
$etablissement = CValue::getOrSession("etablissement", $g);

// Liste des établissements
$etablissements = new CMediusers();
$etablissements = $etablissements->loadEtablissements(PERM_READ);

$smarty = new CSmartyDP();

$smarty->assign("etablissements", $etablissements);
$smarty->assign("etablissement", $etablissement);
$smarty->assign("typeVue", $typeVue);

if ($typeVue) {
  // Liste des Themes
  $doc_theme_id = CValue::getOrSession("doc_theme_id", null);

  // Chargement du theme demandé
  $theme = new CThemeDoc();
  $theme->load($doc_theme_id);
  $theme->loadRefsFwd();

  // Liste des Themes
  $where             = array();
  $where["group_id"] = $etablissement ? "= '$etablissement'" : "IS NULL";
  $listThemes        = $theme->loadList($where, "nom");

  // Création du Template
  $smarty->assign("theme", $theme);
  $smarty->assign("listThemes", $listThemes);
  $smarty->display("vw_edit_themes.tpl");
}
else {
  $maxDeep = CAppUI::conf("dPqualite CChapitreDoc profondeur") - 2;

  // Chargement du chapitre demandé
  $doc_chapitre_id = CValue::getOrSession("doc_chapitre_id", null);
  $chapitre        = new CChapitreDoc;
  $chapitre->load($doc_chapitre_id);
  $chapitre->loadRefsFwd();

  // Chargement du chapitre de navigation
  $nav_chapitre_id = CValue::getOrSession("nav_chapitre_id", null);
  $nav_chapitre    = new CChapitreDoc;
  $nav_chapitre->load($nav_chapitre_id);
  $nav_chapitre->loadRefsFwd();

  if ($nav_chapitre->_id) {
    $nav_chapitre->computeLevel();
    $nav_chapitre->computePath();
  }
  else {
    $nav_chapitre->_level = -1;
  }
  // Liste des Chapitres
  $where             = array();
  $where["group_id"] = $etablissement ? "= '$etablissement'" : "IS NULL";
  $where["pere_id"]  = $nav_chapitre->_id ? "= '$nav_chapitre->_id'" : "IS NULL";
  $listChapitres     = $chapitre->loadList($where, "nom");

  // Création du Template
  $smarty->assign("maxDeep", $maxDeep);
  $smarty->assign("chapitre", $chapitre);
  $smarty->assign("nav_chapitre", $nav_chapitre);
  $smarty->assign("listChapitres", $listChapitres);

  $smarty->display("vw_edit_chapitres.tpl");
}
