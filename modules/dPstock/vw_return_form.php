<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Stock\CProductReturnForm;

CCanDo::checkEdit();

$return_form_id = CValue::get('return_form_id');

$return_form = new CProductReturnForm();
$return_form->load($return_form_id);
$return_form->loadRefAddress();
$return_form->loadRefGroup();
$return_form->loadRefSupplier();
$return_form->loadRefsOutputs();

$pharmacien  = new CMediusers;
$pharmaciens = $pharmacien->loadListFromType(array("Pharmacien"));
if (count($pharmaciens)) {
  $pharmacien = reset($pharmaciens);
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('return_form', $return_form);
$smarty->assign('pharmacien', $pharmacien);
$smarty->display('vw_return_form.tpl');
