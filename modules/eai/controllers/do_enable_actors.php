<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActor;

/**
 * Link transformations
 */
CCanDo::checkAdmin();

$actor_role  = CValue::get("actor_role");
$actor_class = CValue::get("actor_class");
$enable      = CValue::get("enable");

/** @var CInteropActor $actor */
$actor = new $actor_class;
$actors = $actor->getObjects();

$count_actor_desactivate = 0;
$count_actor_activate    = 0;
foreach ($actors as $_actors) {
  if (!$_actors) {
    continue;
  }

  /** @var CInteropActor[] $_actors */
  foreach ($_actors as $_actor) {
    if ($enable && $_actor->actif || !$enable && !$_actor->actif) {
      continue;
    }

    if ($actor_role == "prod" && $_actor->role == "qualif") {
      continue;
    }

    if ($actor_role == "qualif" && $_actor->role == "prod") {
      continue;
    }

    if (!$enable && $_actor->actif) {
      $count_actor_desactivate++;
    }

    if ($enable && !$_actor->actif) {
      $count_actor_activate++;
    }

    $_actor->actif = $enable;

    $_actor->store();
  }
}

CAppUI::setMsg("$actor_class-action-Activate %s", UI_MSG_OK, $count_actor_activate);
CAppUI::setMsg("$actor_class-action-Desactivate %s", UI_MSG_WARNING, $count_actor_desactivate);

echo CAppUI::getMsg();
CApp::rip();