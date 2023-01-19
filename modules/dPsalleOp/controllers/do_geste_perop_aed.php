<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CAnesthPerop;
use Ox\Mediboard\SalleOp\CGestePerop;

$operation_id        = CView::post("operation_id", "ref class|COperation");
$geste_perop_id      = CView::post("geste_perop_id", "ref class|CGestePerop");
$category_id         = CView::post("category_id", "ref class|CAnesthPeropCategorie");
$precision_id        = CView::post("precision_id", "ref class|CGestePeropPrecision");
$precision_valeur_id = CView::post("precision_valeur_id", "ref class|CPrecisionValeur");
$datetime            = CView::post("datetime", "dateTime");
CView::checkin();

if (!$datetime) {
  $interv = new COperation();
  $interv->load($operation_id);
  $datetime = CMbDT::date($interv->_datetime) . " " . CMbDT::time();
}

if ($geste_perop_id && $category_id) {
  $geste = new CGestePerop();
  $geste->load($geste_perop_id);

  $anesth_perop                           = new CAnesthPerop();
  $anesth_perop->libelle                  = $geste->libelle;
  $anesth_perop->geste_perop_id           = $geste_perop_id;
  $anesth_perop->categorie_id             = $category_id;
  $anesth_perop->datetime                 = $datetime;
  $anesth_perop->operation_id             = $operation_id;
  $anesth_perop->geste_perop_precision_id = $precision_id;
  $anesth_perop->precision_valeur_id      = $precision_valeur_id;

  if ($msg = $anesth_perop->store()) {
    return $msg;
  }
}

CAppUI::displayMsg($msg, "CAnesthPerop-msg-create");

echo CAppUI::getMsg();
CApp::rip();
