<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Stock\CProductReturnForm;

CCanDo::checkEdit();

$return_form  = new CProductReturnForm();
$return_forms = $return_form->loadGroupList(null, 'code');

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('return_forms', $return_forms);
$smarty->display('vw_idx_supplier_return.tpl');

