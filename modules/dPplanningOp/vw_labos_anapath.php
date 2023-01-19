<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$smarty = new CSmartyDP();

$smarty->display('vw_labos_anapath');
