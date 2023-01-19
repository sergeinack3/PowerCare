<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Ssr\CActiviteCdARR;

CCanDo::checkRead();

$code     = CValue::get("code");
$activite = CActiviteCdARR::get($code);
if (!$activite->code) {
  CAppUI::stepMessage(UI_MSG_ERROR, "Activit� CdARR '$code' non trouv�e");

  return;
}

$activite->loadRefsElementsByCat();
$activite->loadRefsAllExecutants();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("activite", $activite);

$smarty->display("vw_activite_srr_stats");
