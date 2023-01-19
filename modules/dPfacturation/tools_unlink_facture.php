<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CFactureLiaison;

CCanDo::checkAdmin();

$element_class = CView::get("element_class", "enum list|CConsultation|CEvenementPatient");
$element_id    = CView::get("element_id", "str");
$facture_id    = CView::get("facture_id", "ref class|CFactureCabinet");

CView::checkin();

$facture_liaison = new CFactureLiaison();
$facture_liaison->facture_class = "CFactureCabinet";
$facture_liaison->facture_id    = $facture_id;
$facture_liaison->object_class  = $element_class;
$facture_liaison->object_id     = $element_id;
$facture_liaison->loadMatchingObject();
$msg = $facture_liaison->delete();

$response = array(
  "state" => $msg ? 0 : 1,
  "msg"   => $msg ? : CAppUI::tr("'CFactureLiaison-msg-delete"),
);

CApp::json($response);