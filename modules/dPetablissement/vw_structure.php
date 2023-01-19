<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

// Récupération du groupe selectionné
$group_id = CValue::getOrSession("group_id");

// Fiche établissement
$etab = new CGroups();
$etab->load($group_id);

// Services d'hospitalisation
$service            = new CService();
$service->group_id  = $etab->_id;
$service->cancelled = 0;
$service->externe   = 0;

/** @var CService[] $services */
$services = $service->loadMatchingList("nom");

foreach ($services as $_service) {
  $_service->loadRefsChambres(false);
  foreach ($_service->_ref_chambres as $_chambre) {
    $_chambre->loadRefsLits(false);
  }
}

// Blocs opératoires
$bloc           = new CBlocOperatoire();
$bloc->group_id = $etab->_id;

/** @var CBlocOperatoire[] $blocs */
$blocs = $bloc->loadMatchingList("nom");

foreach($blocs as $_bloc) {
  $_bloc->loadRefsSalles();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("etab"    , $etab);
$smarty->assign("services", $services);
$smarty->assign("blocs"   , $blocs);

$smarty->display("vw_structure.tpl");