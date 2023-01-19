<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CModeleEtiquette;

CCanDo::checkEdit();

$filter_class        = CView::get("filter_class", "str", true);
$modele_etiquette_id = CView::get("modele_etiquette_id", "ref class|CModeleEtiquette", true);

CView::checkin();

// Récupération de la liste suivant l'object_class
$modele_etiquette           = new CModeleEtiquette();
$modele_etiquette->group_id = CGroups::loadCurrent()->_id;

if ($filter_class != "all") {
  $modele_etiquette->object_class = $filter_class;
}

$liste_modele_etiquette = $modele_etiquette->loadMatchingList("nom");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("filter_class", $filter_class);
$smarty->assign("modele_etiquette_id", $modele_etiquette_id);
$smarty->assign("liste_modele_etiquette", $liste_modele_etiquette);
$smarty->display("inc_list_modele_etiquette.tpl");
