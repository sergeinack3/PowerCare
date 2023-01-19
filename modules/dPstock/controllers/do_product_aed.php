<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;

$do = new CDoObjectAddEdit('CProduct');

if (CValue::post("_duplicate")) {
  $do->doBind();
  $product       = $do->_old;
  $product->code .= "-copie";
  $product->name .= " (Copie)";
  $product->_id  = null;

  if ($msg = $product->store()) {
    CAppUI::setMsg($msg);
  }
  else {
    // Redirection vers le nouveau 
    $_GET["product_id"] = $product->_id;
  }
}
else {
  $do->doIt();
}
