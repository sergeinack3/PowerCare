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
use Ox\Mediboard\Labo\CCatalogueLabo;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$catalogue_labo_id = CView::get("catalogue_labo_id", "ref class|CCatalogueLabo", true);

CView::checkin();

// Chargement du catalogue demandé
$catalogue = new CCatalogueLabo();
$catalogue->load($catalogue_labo_id);

// Fonction de l'utilisateur courant
if (!$catalogue->_id) {
  $user = CMediusers::get();
  $catalogue->function_id = $user->function_id;
}

// Liste des fonctions disponibles
$functions = new CFunctions();
$order = "text";
$functions = $functions->loadListWithPerms(PERM_EDIT, null, $order);

// Chargement de tous les catalogues
$where = array(
  "function_id" => "IS NULL OR function_id ".CSQLDataSource::prepareIn(array_keys($functions)),
  "pere_id"     => "IS NULL"
);

$listCatalogues = $catalogue->loadList($where, "identifiant");

foreach ($listCatalogues as $_catalogue) {
  $_catalogue->computeLevel();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("catalogue"     , $catalogue);
$smarty->assign("functions"     , $functions);
$smarty->assign("listCatalogues", $listCatalogues);

$smarty->display("inc_edit_catalogue");
