<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureLiaison;
use Ox\Mediboard\Patients\CEvenementPatient;

CCanDo::checkEdit();

$facture_guid = CView::get("facture_guid", "str");
CView::checkin();

$facture = CStoredObject::loadFromGuid($facture_guid);

if ($facture->annule || $facture->cloture) {
  return;
}

$liaisons = $facture->loadRefsLiaisons();
foreach ($liaisons as $_liaison) {
  /* @var CFactureLiaison $_liaison*/
  $object = $_liaison->loadRefFacturable();
  if ($object instanceof CConsultation || $object instanceof CEvenementPatient) {
    $object->valide = 0;
    if ($msg = $object->store()) {
      CAppUI::displayAjaxMsg($msg);
    }
  }

  //Suppression des liaisons dans le cas de plusieurs liaisons à la facture
  if (count($liaisons) > 1) {
    if ($msg = $_liaison->delete()) {
      CAppUI::displayAjaxMsg($msg);
    }
  }
}

//Annulation de la facture
$facture->annule = 1;
$facture->definitive = 1;
if ($msg = $facture->store()) {
  return $msg;
}

// Dissociation des liaisons de facture
CAppUI::displayAjaxMsg("CFactureLiaison.Manager objects disassociated to the $facture->_class");