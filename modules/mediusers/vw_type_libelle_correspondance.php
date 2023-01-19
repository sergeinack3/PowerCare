<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkAdmin();

$smarty = new CSmartyDP();
$smarty->assign('types', CUser::$types);
$smarty->display('vw_type_libelle_correspondance.tpl');