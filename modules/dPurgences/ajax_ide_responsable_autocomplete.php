<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$field    = CView::get("field", "str default|ide_responsable_id_view");
$keywords = CView::get($field, "str");

CView::checkin();
CView::enableSlave();

$group = CGroups::loadCurrent();

if ($keywords == "") {
  $keywords = "%%";
}
$mediuser = new CMediusers();
$matches  = $mediuser->loadListFromType(array("Infirmière"), PERM_READ, $group->service_urgences_id, $keywords, true, true);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("keywords", $keywords);
$smarty->assign("matches", $matches);

$smarty->display("inc_autocomplete_ide_responsable");