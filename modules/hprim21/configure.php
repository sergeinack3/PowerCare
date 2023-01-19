<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Configuration du module Hprim21
 */
CCanDo::checkAdmin();

$source_name    = "hprim21-" . CGroups::loadCurrent()->_id;
$hprim21_source = CExchangeSource::get($source_name, CSourceFTP::TYPE, true);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("hprim21_source" , $hprim21_source);
$smarty->display("configure.tpl");

