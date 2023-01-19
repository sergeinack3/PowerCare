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
use Ox\Core\CValue;
use Ox\Mediboard\Labo\CCatalogueLabo;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkRead();

$catalogue_labo_id = CValue::getOrSession("catalogue_labo_id");
$typeListe  = CValue::getOrSession("typeListe");

// Liste des fonctions disponibles
$functions = new CFunctions();
$order = "text";
$functions = $functions->loadListWithPerms(PERM_EDIT, null, $order);

// Chargement du catalogue demandé
$catalogue = new CCatalogueLabo();
$catalogue->load($catalogue_labo_id);
$catalogue->loadRefs();

// Chargement de tous les catalogues
$where = array();
$where["pere_id"] = "IS NULL";
$where[] = "function_id IS NULL OR function_id ".CSQLDataSource::prepareIn(array_keys($functions));
$where["obsolete"] = " = '0'";
$order = "identifiant";
$listCatalogues = $catalogue->loadList($where, $order);
foreach ($listCatalogues as &$_catalogue) {
  $_catalogue->loadRefsDeep();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listCatalogues", $listCatalogues);
$smarty->assign("catalogue"     , $catalogue    );

$smarty->display("inc_vw_catalogues");
