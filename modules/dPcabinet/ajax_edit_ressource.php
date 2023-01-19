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
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$ressource_cab_id = CView::get("ressource_cab_id", "ref class|CRessourceCab");
$function_id      = CView::getRefCheckEdit("function_id", "ref class|CFunctions");

CView::checkin();

$ressource = new CRessourceCab();
$ressource->load($ressource_cab_id);

if (!$ressource->_id) {
  $ressource->function_id = $function_id;
  $ressource->owner_id    = CMediusers::get()->_id;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("ressource", $ressource);

$smarty->display("inc_edit_ressource.tpl");
