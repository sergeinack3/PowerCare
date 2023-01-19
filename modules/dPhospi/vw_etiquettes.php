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
use Ox\Mediboard\Hospi\CModeleEtiquette;

CCanDo::checkEdit();

$modele_etiquette_id = CView::get("modele_etiquette_id", "ref class|CModeleEtiquette", true);
$filter_class        = CView::get("filter_class", "str default|all", true);

CView::checkin();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("modele_etiquette_id", $modele_etiquette_id);
$smarty->assign("classes", CModeleEtiquette::getContextClasses());
$smarty->assign("filter_class", $filter_class);
$smarty->display("vw_etiquettes");
