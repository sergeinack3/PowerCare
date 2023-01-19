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
use Ox\Mediboard\Stock\CProductSelection;

CCanDo::checkEdit();

$selection_id = CValue::getOrSession('selection_id');

$selection = new CProductSelection();

if ($selection->load($selection_id)) {
  $selection->loadRefsBack();

  foreach ($selection->_back["selection_items"] as $_item) {
    $_item->updateFormFields();
  }
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('selection', $selection);
$smarty->display('inc_form_selection.tpl');
