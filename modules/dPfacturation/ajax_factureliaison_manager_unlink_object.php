<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Facturation\CFactureLiaison;
use Ox\Mediboard\Patients\CEvenementPatient;

CCanDo::checkEdit();

$facture_guid = CView::get("facture_guid", "str");
$object_guid = CView::get("object_guid", "str");

CView::checkin();

/** @var $facture CFactureCabinet|CFactureEtablissement */
$facture = CMbObject::loadFromGuid($facture_guid);
$facture->loadRefsObjects();
$count_obj = 0;
$count_obj += (is_countable($facture->_ref_evts)) ? count($facture->_ref_evts) : 0;
$count_obj += (is_countable($facture->_ref_sejours)) ? count($facture->_ref_sejours) : 0;
$count_obj += (is_countable($facture->_ref_consults)) ? count($facture->_ref_consults) : 0;

$object  = CMbObject::loadFromGuid($object_guid);

if ($object instanceof CConsultation) {
  CAccessMedicalData::logAccess($object);
}

if ($object instanceof CConsultation || $object instanceof CEvenementPatient) {
  $object->valide = 0;
  if ($msg = $object->store()) {
    CAppUI::displayAjaxMsg($msg);
  }
}

if ($count_obj === 1) {
  $facture->annule = 1;
  $facture->definitive = 1;
  if ($msg = $facture->store()) {
    CAppUI::displayAjaxMsg($msg);
  }
  $_facture = CMbObject::loadFromGuid($facture_guid);
}
else {
  $facture->loadRefsLiaisons();
  /** @var $_liaison CFactureLiaison */
  foreach ($facture->_ref_liaisons as $_liaison) {
    if ($_liaison->object_class !== $object->_class || $_liaison->object_id !== $object->_id) {
      continue;
    }
    $_liaison->delete();
  }
}

CAppUI::displayAjaxMsg(CAppUI::tr("CFactureLiaison.Manager $object->_class disassociated to the $facture->_class"));
