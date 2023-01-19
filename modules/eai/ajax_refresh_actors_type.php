<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActor;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$actor_class = CView::get('actor_class', 'str');

$all_actors  = CView::get('all_actors', 'str');
CView::checkin();

/** @var CInteropActor $actor */
$actor = new $actor_class();
$group = CGroups::loadCurrent()->_id;

$actors = $all_actors ? $actor->getObjectsByClass($actor_class, false, false) : $actor->getObjectsByClass($actor_class,true,true,$group);

$smarty = new CSmartyDP();
$smarty->assign('actors', $actors);
$smarty->assign('type_actor', $actor_class);
$smarty->assign('role_instance', CAppUI::conf('instance_role'));
$smarty->display('inc_refresh_actors_type.tpl');
