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
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

$function_id = CView::getRefCheckEdit("function_id", "ref class|CFunctions", true);

CView::checkin();

$ressource = new CRessourceCab();
$ressource->function_id = $function_id;

$ressources = $ressource->loadMatchingList("libelle");

$total = $ressource->countMatchingList();

$function = new CFunctions();
$function->load($function_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("ressources", $ressources);
$smarty->assign("function"  , $function);

$smarty->display("inc_list_ressources.tpl");
