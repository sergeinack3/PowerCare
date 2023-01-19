<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

/**
 * Class CRAD48DelegatedHandler
 * RAD48 Delegated Handler
 */
class CRAD3DelegatedHandler extends CITIDelegatedHandler {
  static $handled        = array ("CConsultation", "CPrescriptionLineElement");
  protected $profil      = "SWF";
  protected $message     = "ORM";
  protected $transaction = "RAD3";

  /**
   * @inheritDoc
   */
  static function isHandled(CStoredObject $mbObject) {
    return in_array($mbObject->_class, self::$handled);
  }

  /**
   * @see parent::onAfterStore()
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    if ($object instanceof CConsultation) {
      /** @var CConsultation $consultation */
      $consultation = $object;
      $praticien = $consultation->loadRefPraticien();
      if (!$praticien || $praticien && !$praticien->_id) {
        return false;
      }

      $function = $praticien->loadRefFunction();
      if (!$function || $function && !$function->_id) {
        return false;
      }

      // On n'envoie pas si pas la config.
      if (!CAppUI::gconf("ihe RAD send_order_by_consult", $function->group_id)) {
        return false;
      }

      $functions = CAppUI::conf("ihe RAD-3 function_ids");
      $functions = explode("|", $functions);

      if (!in_array($function->_id, $functions)) {
        return false;
      }

      $code = "O01";

      if (!$this->isMessageSupported($this->message, $code, $consultation->_receiver)) {
        return false;
      }

      $this->sendITI($this->profil, $this->transaction, $this->message, $code, $consultation);
    }

    if ($object instanceof CPrescriptionLineElement) {
      $prescription_line_element = $object;

      // On envoie le message uniquement si la ligne est signée
      if (!$prescription_line_element->fieldModified("signee", "1")) {
        return false;
      }

      // Si la configuration send_evenement_to_mbdmp est activé, on envoi pas les prescription à AppFine
      if (CModule::getActive("appFineClient") && $prescription_line_element->_receiver->_configs['send_evenement_to_mbdmp']) {
        return false;
      }

      $elt_category = $prescription_line_element->loadRefElement()->loadRefCategory();
      // On envoie le message uniquement si l'élèment fait référence à la catégorie "Imagerie"
      if ($elt_category->chapitre != "imagerie") {
        return false;
      }

      $code = "O01";

      if (!$this->isMessageSupported($this->message, $code, $prescription_line_element->_receiver)) {
        return false;
      }

      $this->sendITI($this->profil, $this->transaction, $this->message, $code, $prescription_line_element);
    }

    return true;
  }

  /**
   * @see parent::onBeforeDelete()
   */
  function onBeforeDelete(CStoredObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }

    return true;
  }

  /**
   * @see parent::onAfterDelete()
   */
  function onAfterDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    if ($object instanceof CConsultation) {
      $consultation = $object;
      if (!$consultation->_old->element_prescription_id) {
        return false;
      }

      $function = $consultation->loadRefPraticien()->loadRefFunction();
      // On n'envoie pas si pas la config.
      if (!CAppUI::conf("ihe RAD send_order_by_consult", $function->group_id)) {
        return false;
      }

      $element  = $consultation->_old->loadRefElementPrescription();
      $category = $element->loadRefCategory();

      if (!$category) {
        return false;
      }

      switch ($category->chapitre) {
        case "imagerie":
          $code = "O01";
          if (!$this->isMessageSupported($this->message, $code, $consultation->_receiver)) {
            return;
          }

          $this->sendITI($this->profil, $this->transaction, $this->message, $code, $consultation);

          break;
        default:
          return false;
      }
    }

    return true;
  }
}
