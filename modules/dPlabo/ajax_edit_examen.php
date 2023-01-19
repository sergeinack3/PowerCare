<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Labo\CCatalogueLabo;
use Ox\Mediboard\Labo\CExamenLabo;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

$examen_labo_id    = CView::get("examen_labo_id", "ref class|CExamenLabo", true);
$catalogue_labo_id = CView::get("catalogue_labo_id", "ref class|CCatalogueExamen");

CView::checkin();

// Chargement de l'examen demandé
$examen = new CExamenLabo;

// Chargement du catalogue demandé
if ($examen->load($examen_labo_id)) {
  $examen->loadRefs();
  $examen->getSiblings();
  $examen->getRootCatalogue();
  foreach ($examen->_ref_siblings as &$_sibling) {
    $_sibling->loadClassification();
  }
  $examen->loadClassification();
  $catalogue =& $examen->_ref_catalogue_labo;
}
else {
  $catalogue = new CCatalogueLabo;
  $catalogue->load($catalogue_labo_id);
  $examen->catalogue_labo_id = $catalogue->_id;
}

$catalogue->loadRefs();

$groups = CGroups::loadGroups();
foreach ($groups as &$group) {
  $group->loadFunctions(null);
  foreach ($group->_ref_functions as $keyFunc => &$function) {
    if ($function->getPerm(PERM_EDIT)) {
      $function->loadRefsUsers();
    }
    else {
      unset($group->_ref_functions[$keyFunc]);
    }
  }
}

// Liste des fonctions disponibles
$functions = new CFunctions();
$order = "text";
$functions = $functions->loadListWithPerms(PERM_EDIT, null, $order);

// Chargement de tous les catalogues
$where = array();
$where["pere_id"] = "IS NULL";
$where[] = "function_id IS NULL OR function_id ".CSQLDataSource::prepareIn(array_keys($functions));
$order = "identifiant";
$listCatalogues = $catalogue->loadList($where, $order);
foreach ($listCatalogues as &$_catalogue) {
  $_catalogue->loadRefsDeep();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("groups"        , $groups);
$smarty->assign("examen"        , $examen);
$smarty->assign("catalogue"     , $catalogue);
$smarty->assign("listCatalogues", $listCatalogues);

$smarty->display("inc_edit_examen");
