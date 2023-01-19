<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Qualite\CFicheEi;

CCanDo::checkRead();

$smarty = new CSmartyDP();
$smarty->assign("fiche", new CFicheEi);
$smarty->display("vw_legende_criticite.tpl"); 
