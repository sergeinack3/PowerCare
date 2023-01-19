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

CCanDo::check();

$object_class    = CView::get("object_class", "str");
$object_id       = CView::get("object_id", "ref class|$object_class");
$custom_function = CView::get("custom_function", "str");

CView::checkin();

$modele_etiquette               = new CModeleEtiquette();
$modele_etiquette->object_class = $object_class;
$modele_etiquette->group_id     = CGroups::loadCurrent()->_id;

$modeles_etiquettes = $modele_etiquette->loadMatchingList();

$smarty = new CSmartyDP();

$smarty->assign("modeles_etiquettes", $modeles_etiquettes);
$smarty->assign("object_class", $object_class);
$smarty->assign("object_id", $object_id);
$smarty->assign("custom_function", $custom_function);

$smarty->display("inc_choose_modele_etiquette.tpl");
