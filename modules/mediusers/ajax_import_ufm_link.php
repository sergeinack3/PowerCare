<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$import_specs = array(
  'Nom utilisateur' => 'str',
  'Nom'      => 'str',
  'Prénom'   => 'str',
  'UF médicale' => 'str notNull',
);

$smarty = new CSmartyDP();
$smarty->assign('import_specs', $import_specs);
$smarty->display('inc_import_ufm_link.tpl');