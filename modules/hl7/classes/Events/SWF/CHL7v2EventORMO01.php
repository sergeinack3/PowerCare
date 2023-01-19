<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SWF;

use Ox\AppFine\Client\CAppFineClientOrderItem;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

/**
 * Class CHL7v2EventORMO01
 * O01 - Order Message
 */
class CHL7v2EventORMO01 extends CHL7v2EventORM implements CHL7EventORMO01 {
  /** @var string */
  public $code = "O01";

  /**
   * Build O01 event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    parent::build($object);
    if (CModule::getActive("appFineClient") && $this->_receiver->_configs["send_evenement_to_mbdmp"]) {
      $sejour       = new CSejour();
      $patient      = new CPatient();
      $consultation = new CConsultation();

      switch ($object->_class) {
        case "CSejour":
          $sejour  = $object;
          $patient = $sejour->loadRefPatient();
          $sejour->loadRefPraticien();
          break;
        case "CConsultation":
          $consultation = $object;
          $patient      = $consultation->loadRefPatient();
          break;
        case "CPatient":
          $patient = $object;
          break;
        default:
      }

      $this->addPID($patient, $sejour);
      $this->addPV1($sejour);

      if (isset($object->_orders_items)) {
        /** @var CAppFineClientOrderItem $_order_item */
        foreach ($object->_orders_items as $_order_item) {

          $_order_item->loadLastLog();
          $_order_item->_context = "CFile";
          $this->addORC($_order_item);
          $this->addOBR($_order_item);
          $this->addOBX($_order_item);
        }
      }
    }
    else {
      if ($object instanceof CConsultation) {
        /** @var Cconsultation $object */
        $object->loadLastLog();
        $object->loadRefPlageConsult();
        $object->loadRefPraticien();
        $object->loadRefElementPrescription();
        $object->loadRefPatient();
        $object->loadRefSejour();

        // Cas de suppression de consultation
        if (!$object->_id) {
          $object = $object->_old;
        }

        $this->addORC($object);
        $this->addOBR($object);
      }

      if ($object instanceof CPrescriptionLineElement) {
        $object->loadRefPraticien();

        /** @var CPrescriptionLineElement $object */
        $prescription = $object->loadRefPrescription();
        /** @var CSejour|CConsultation $target */
        $target       = $prescription->loadRefObject();
        $target->loadLastLog();
        $target->loadRefPraticien();
        $patient = $target->loadRefPatient();

        $sejour       = new CSejour();
        $consultation = new CConsultation();
        switch ($prescription->object_class) {
          case "CSejour":
            $sejour  = $target;
            break;

          case "CConsultation":
            $consultation = $target;
            $consultation->loadRefPlageConsult();
            break;
          default:
            return;
        }

        $this->addPID($patient, $sejour);
        $this->addPV1($sejour);
        $this->addORC($object);
        foreach ($object->loadRefsPrises() as $_prise) {
          $this->addOBR($object, $_prise);
        }
      }
    }
  }
}
