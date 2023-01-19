<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CRessourceCab;

CCanDo::checkEdit();

$function_id = CView::getRefCheckEdit("function_id", "ref class|CFunctions", true);
$date        = CView::get("date", "date default|now", true);

CView::checkin();

$ressource = new CRessourceCab();
$ressource->function_id = $function_id;
$ressource->actif = 1;

$ressources = $ressource->loadMatchingList("libelle");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("ressources", $ressources);

$smarty->display("inc_vw_plannings_ressources.tpl");
