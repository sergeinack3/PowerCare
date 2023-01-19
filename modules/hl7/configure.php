<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Mediboard\System\CExchangeSource;

CCanDo::checkAdmin();

$hl7v2_source = CExchangeSource::get("hl7v2", CSourceFTP::TYPE, true, null, false);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("hl7v2_source" , $hl7v2_source);
$smarty->display("configure.tpl");

