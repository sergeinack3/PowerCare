<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Qualite\CChapitreDoc;
use Ox\Mediboard\Qualite\CDocGed;

CCanDo::checkRead();

$doc_ged_id  = CValue::getOrSession("doc_ged_id");
$theme_id    = CValue::getOrSession("theme_id");
$chapitre_id = CValue::getOrSession("chapitre_id");
$sort_by     = CValue::getOrSession("sort_by", "date");
$sort_way    = CValue::getOrSession("sort_way", "DESC");
$keywords    = CValue::get("keywords");
$first       = intval(CValue::get("first", 0));

$group = CGroups::loadCurrent();

// Procédure active et non annulée
$where          = array();
$where[]        = "annule = '0' OR annule IS NULL";
$where[]        = "doc_ged.group_id = '$group->_id' OR doc_ged.group_id IS NULL";
$where["actif"] = "= '1'";
$where[]        = "date = (SELECT max(date) FROM doc_ged_suivi d1 WHERE d1.doc_ged_id = doc_ged.doc_ged_id AND actif = '1')";
if ($theme_id) {
  $where["doc_theme_id"] = "= '$theme_id'";
}
if ($chapitre_id) {
  $chapitre = new CChapitreDoc();
  $chapitre->load($chapitre_id);
  $chapitre->loadChapsDeep();
  $where["doc_ged.doc_chapitre_id"] = CSQLDataSource::prepareIn($chapitre->_chaps_and_subchaps);
}
if ($keywords) {
  $where["doc_ged.titre"] = "LIKE '%$keywords%'";
}
$ljoin                   = array();
$ljoin["doc_ged_suivi"]  = "doc_ged.doc_ged_id = doc_ged_suivi.doc_ged_id";
$ljoin["doc_categories"] = "doc_ged.doc_categorie_id = doc_categories.doc_categorie_id";
$ljoin["doc_chapitres"]  = "doc_ged.doc_chapitre_id = doc_chapitres.doc_chapitre_id";
$group                   = "doc_ged.doc_ged_id";
if ($sort_by == 'ref') {
  $sort_way = "ASC";
  if (CAppUI::conf("dPqualite CDocGed _reference_doc")) {
    $sort_by = $group = "doc_categories.code, doc_chapitres.code, doc_ged.num_ref";
  }
  else {
    $sort_by = $group = "doc_chapitres.code, doc_categories.code, doc_ged.num_ref";
  }
}
else {
  // Tri par date
  $sort_way = "DESC";
  $sort_by  = " doc_ged_suivi.$sort_by";
}

$procedure = new CDocGed();

/** @var CDocGed[] $list_procedures */
$list_procedures = $procedure->loadList($where, "$sort_by $sort_way", "$first,20", $group, $ljoin);


foreach ($list_procedures as $curr_proc) {
  $curr_proc->loadRefs();
  $curr_proc->loadLastActif();
}

$count_procedures = $procedure->countList($where, null, $ljoin);

if ($count_procedures >= 20) {
  $pages = range(0, $count_procedures, 20);
}
else {
  $pages = array();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("procedures", $list_procedures);
$smarty->assign("count_procedures", $count_procedures);
$smarty->assign("pages", $pages);
$smarty->assign("first", $first);

$smarty->display("inc_list_procedures.tpl");
