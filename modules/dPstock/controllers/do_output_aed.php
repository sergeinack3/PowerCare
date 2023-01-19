<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductReference;
use Ox\Mediboard\Stock\CProductReturnForm;
use Ox\Mediboard\Stock\CProductStockGroup;

$do = new CDoObjectAddEdit('CProductOutput');

if (CValue::post("_create_form")) {
  $group_id = CProductStockGroup::getHostGroup();

  $reference_id            = CValue::post("_reference_id");
  $reference               = new CProductReference;
  $reference->reference_id = $reference_id;

  if (!$reference_id || !$reference->loadMatchingObject()) {
    CAppUI::setMsg("Impossible de créer l'article, la réference n'existe pas", UI_MSG_ERROR);
  }

  $where = array(
    "product_return_form.supplier_id" => "= '$reference->societe_id'",
    "product_return_form.status"      => "= 'new'",
    "product_return_form.group_id"    => "= '$group_id'",
  );

  $form  = new CProductReturnForm();
  $forms = $form->loadList($where);

  // If no order found
  if (count($forms) == 0) {
    $form->datetime    = CMbDT::dateTime();
    $form->status      = "new";
    $form->supplier_id = $reference->societe_id;
    $form->group_id    = $group_id;

    if ($msg = $form->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
  }
  else {
    $form = reset($forms);
  }

  $_POST["return_form_id"] = $form->_id;
}

$do->doIt();
