<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\Stock\CProduct;

CCanDo::checkAdmin();

// Implants Physiol Micro AY /////////////
$where         = array();
$where["code"] = "LIKE 'MICRO AY %'";

$product      = new CProduct;
$list_product = $product->loadList($where);

CAppUI::stepAjax(count($list_product) . " produit(s) à remplacer (Physiol Micro AY)");

$errors = 0;
foreach ($list_product as $_product) {
  if (!preg_match('/^MICRO AY ([+-])(\d{2}).([05])$/', $_product->code, $matches)) {
    continue;
  }

  $old_code = $_product->code;

  $dioptrie_sign  = ($matches[1] === "+") ? "1" : "2";
  $_product->code = "2808" . $dioptrie_sign . $matches[2] . $matches[3];

  CAppUI::stepAjax(" Conversion: \"$old_code\" => \"{$_product->code}\"");
  if ($msg = $_product->store()) {
    CAppUI::stepAjax("Problème dans la conversion :" . $msg, UI_MSG_WARNING);
    $errors++;
  }
}


// Implants ALCON SN60WF ///////////////
$where         = array();
$where["code"] = "LIKE 'SN60WF +%'";

$product      = new CProduct;
$list_product = $product->loadList($where);

CAppUI::stepAjax(count($list_product) . " produit(s) à remplacer (ALCON SN60WF)");

$errors = 0;
foreach ($list_product as $_product) {
  if (!preg_match('/(SN60WF)\ \+([0123]\d)(?:\.([05]))?/', $_product->code, $matches)) {
    continue;
  }

  $old_code = $_product->code;

  $_product->code = $matches[1] . "." . $matches[2] . (isset($matches[3]) ? $matches[3] : "0");

  CAppUI::stepAjax(" Conversion: \"$old_code\" => \"{$_product->code}\"");
  if ($msg = $_product->store()) {
    CAppUI::stepAjax("Problème dans la conversion :" . $msg, UI_MSG_WARNING);
    $errors++;
  }
}

CAppUI::stepAjax("Fin de conversion avec " . $errors . " erreurs", $errors ? UI_MSG_WARNING : UI_MSG_OK);
CApp::rip();
