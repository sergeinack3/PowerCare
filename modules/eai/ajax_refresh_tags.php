<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

/**
 * Formats available
 */

CCanDo::checkRead();

$actor_guid = CView::get("actor_guid", "str", true);

CView::checkin();

$actor = CMbObject::loadFromGuid($actor_guid);
$actor->getInstanceTags();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor", $actor);
$smarty->display("inc_tags.tpl");

