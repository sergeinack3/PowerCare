<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$date                 = CView::get("date", 'date default|now', true);
$object_class         = CView::get("object_class", 'str notNull');
$object_id            = CView::get("object_id", 'ref meta|object_class notNull', true);
$executant_id         = CView::get('executant_id', 'ref class|CMediusers');
$execution            = CView::get('execution', 'dateTime');
$modal                = CView::get('modal', 'bool default|0');
$code                 = CView::get("code", "str");
$coefficient          = CView::get('coefficient', 'float');
$order_col            = CView::get('order_col', 'enum list|code|execution|executant_id default|code', true);
$order_way            = CView::get('order_way', 'enum list|ASC|DESC default|ASC', true);
$display              = CView::get('display', 'str');
$target               = CView::get('target', 'str default|listActesNGAP');
$filter_executant_id  = CView::get('filter_executant_id', 'ref class|CMediusers');
$filter_function_id   = CView::get('filter_function_id', 'ref class|CFunctions');
$filter_date_min      = CView::get('filter_date_min', 'date');
$filter_date_max      = CView::get('filter_date_max', 'date');
$filter_facturable    = CView::get('filter_facturable', 'bool');
$page                 = CView::get('page', 'num default|0', true);
$show_tarifs          = CView::get('show_tarifs', 'bool default|0');
$refresh_list         = CView::get('refresh_list', 'bool default|0');
CView::checkin();

$user = CMediusers::get();

// Chargement de la consultation
/** @var CCodable $object */
$object = new $object_class;
$object->load($object_id);

$filter_executant_id = $filter_executant_id ? (int)$filter_executant_id : null;
$filter_function_id = $filter_function_id ? (int)$filter_function_id: null;

CAccessMedicalData::logAccess($object);

$object->countActes();

$object->loadRefsActesNGAP(
  $filter_facturable, $order_col, $order_way, "$page, 10",
  $filter_executant_id, $filter_function_id, $filter_date_min, $filter_date_max
);

$praticien = $object->loadRefPraticien();
if ($executant_id) {
  $praticien = CMediusers::get($executant_id);
}

if (!$refresh_list) {
    $object->isCoded();
}
$object->loadRefPatient();

$date_now = CMbDT::date();

if ($object instanceof CConsultation) {
  $object->loadRefSejour()->loadDiagnosticsAssocies();
}

// Initialisation d'un acte NGAP
$acte_ngap = false;
if ($executant_id) {
  $acte_ngap = CActeNGAP::createEmptyFor($object, $praticien);
}
else {
  $acte_ngap = CActeNGAP::createEmptyFor($object);
}

if ($execution && $acte_ngap) {
  $acte_ngap->execution = $execution;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("acte_ngap"      , $acte_ngap);
$smarty->assign("object"         , $object);
$smarty->assign("subject"        , $object);
if ($object_class == "CConsultation") {
  $smarty->assign("_is_dentiste"   , $object->_is_dentiste);
}
elseif ($object_class == "COperation") {
  $object->loadRefChir();
  $smarty->assign("_is_dentiste"   , $object->_ref_chir->isDentiste());
}
else {
  $smarty->assign("_is_dentiste", false);
}
$smarty->assign('executant_id', $executant_id);
$smarty->assign('execution', $execution);
$smarty->assign('code', $code);
$smarty->assign('coefficient', $coefficient);
$smarty->assign('modal', $modal);
$smarty->assign('order_col', $order_col);
$smarty->assign('order_way', $order_way);
$smarty->assign('page', $page);
$smarty->assign('target', $target);
$smarty->assign('display', $display);
$smarty->assign('show_tarifs', $show_tarifs);

$smarty->display("inc_codage_ngap.tpl");
