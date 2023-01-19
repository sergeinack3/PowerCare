<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CEditPdf;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$factures_guid = CView::get("factures_guid", "str");
$debug_guid    = CView::get("debug_guid", "str");
$action        = CView::get("action", "enum list|print|close|open|cotationopen default|");

CView::checkin();
$factures_by_class = CStoredObject::loadFromGuids($factures_guid);
/** @var $factures CFactureCabinet[]|CFactureEtablissement[] */
$factures = array();
foreach ($factures_by_class as $_factures) {
  $factures = array_merge($factures, $_factures);
}

$saved_facture = 0;
switch ($action) {
  case "open":
    foreach ($factures as $_facture) {
      if (!$_facture->_id || !$_facture->cloture || $_facture->definitive) {
        continue;
      }

      $_facture->cloture = "";
      if ($msg = $_facture->store()) {
        CAppUI::displayAjaxMsg($msg, UI_MSG_ALERT);
        continue;
      }
      $saved_facture++;
    }
    break;
  case "cotationopen":
    foreach ($factures as $_facture) {
      if (!$_facture->_id || $_facture->cloture || $_facture->definitive) {
        continue;
      }
      $_facture->loadRefsObjects();
      foreach ($_facture->_ref_consults as $_consult) {
        $_consult->valide = 0;
        if ($msg = $_consult->store()) {
          CAppUI::displayAjaxMsg($msg, UI_MSG_ALERT);
        }
      }
      foreach ($_facture->_ref_evts as $_evt) {
        $_evt->valide = 0;
        if ($msg = $_consult->store()) {
          CAppUI::displayAjaxMsg($msg, UI_MSG_ALERT);
        }
      }
      $saved_facture++;
    }
    break;
  case "close":
    foreach ($factures as $_facture) {
      if (!$_facture->_id || $_facture->cloture || $_facture->definitive) {
        continue;
      }

      $_facture->cloture = CMbDT::date();
      if ($msg = $_facture->store()) {
        CAppUI::displayAjaxMsg($msg, UI_MSG_ALERT);
      }
      $saved_facture++;
    }
    break;
  case "print":
    $editPdf = new CEditPdf();
    $editPdf->factures = array();
    foreach ($factures as $_facture) {
      if (!$_facture->cloture) {
        continue;
      }
      $_facture->bill_date_printed = CMbDT::dateTime();
      $_facture->bill_user_printed = CMediusers::get()->_id;

      if ($msg = $_facture->store()) {
        CAppUI::displayAjaxMsg($msg, UI_MSG_ALERT);
      }
      $editPdf->factures[$_facture->_id] = $facture;
    }

    $editPdf->editFactureBVR();
    break;
}
CAppUI::displayAjaxMsg(CAppUI::tr("CFacture.n invoice saved|pl", $saved_facture));