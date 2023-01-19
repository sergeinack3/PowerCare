<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Facturation\CFactureLiaison;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$facture_guid = CView::get("facture_guid", "str");
$object_guid = CView::get("object_guid", "str");

CView::checkin();

/** @var $object CSejour|CConsultation|CEvenementPatient */
$object = CMbObject::loadFromGuid($object_guid);

// Controle la non-presence de factures valides pour l'objet
$where_facture = array("annule" => "= 0");
$facture_etab = new CFactureEtablissement();
$facture_cabinet = new CFactureCabinet();
$factures_etab = $facture_etab->loadList($where_facture);
$factures_cabinet = $facture_cabinet->loadList($where_facture);
if ((is_countable($factures_cabinet) && count($factures_cabinet) > 0)
  || (is_countable($factures_etab) && count($factures_etab) > 0)
  || !$object->_id) {
  CApp::rip();
}

$facture = CMbObject::loadFromGuid($facture_guid);
$facture_liaison = new CFactureLiaison();
$facture_liaison->object_id = $object->_id;
$facture_liaison->object_class = $object->_class;
$facture_liaison->facture_id = $facture->_id;
$facture_liaison->facture_class = $facture->_class;
if ($msg = $facture_liaison->store()) {
  CAppUI::displayAjaxMsg($msg);
}

$object->valide = 1;
if ($msg = $object->store()) {
  CAppUI::displayAjaxMsg($msg);
}

CAppUI::displayAjaxMsg(CAppUI::tr("CFactureLiaison.Manager $object->_class associated from the $facture->_class"));
