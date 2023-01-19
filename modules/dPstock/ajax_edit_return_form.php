<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductReturnForm;

CCanDo::checkRead();

$return_form_id = CValue::get('return_form_id');

// Loads the expected Order
$return_form = new CProductReturnForm();
$return_form->load($return_form_id);

if (!$return_form->_id) {
  $return_form->datetime = CMbDT::dateTime();
}

$return_form->loadRefsOutputs();

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('return_form', $return_form);
$smarty->display('inc_edit_return_form.tpl');