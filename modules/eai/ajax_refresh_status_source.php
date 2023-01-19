<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCando;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActor;

CCanDo::checkRead();

$actor_guid = CView::get('actor_guid', 'str');
CView::checkin();

/** @var CInteropActor $actor */
$actor = CMbObject::loadFromGuid($actor_guid);

if (!$actor || !$actor->_id) {
    CAppUI::stepAjax('CInteropActor-msg-No actor', UI_MSG_ERROR);
}

$actor->loadRefsExchangesSources();

$smarty = new CSmartyDP();
$smarty->assign('_actor', $actor);
$smarty->assign('accessibility', 1);
$smarty->display('inc_refresh_status_source.tpl');
