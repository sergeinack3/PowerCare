<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Eai\CSpecialtyAsip;

CCanDo::checkRead();

$smarty = new CSmartyDP();
$smarty->assign('specs', (new CSpecialtyAsip())->loadList(null, 'libelle ASC'));
$smarty->display('inc_display_specs');