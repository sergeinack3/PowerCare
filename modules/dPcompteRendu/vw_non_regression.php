<?php 
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;

$smarty = new CSmartyDP();

$filtre = new CCompteRendu();

$filtre->user_id = CMediusers::get()->_id;
$filtre->_ref_user = CMediusers::get();

$smarty->assign("filtre", $filtre);

$smarty->display("vw_non_regression");