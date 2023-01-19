<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CSipHprimXMLObjectHandler
 * SIP H'XML Object handler
 */

class CSipHprimXMLObjectHandler extends CHprimXMLObjectHandler {
  static $handled = array ("CPatient", "CCorrespondantPatient");

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    return in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }
    
    $receiver = $object->_receiver;
    
    if ($object instanceof CCorrespondantPatient) {
      $patient = $object->loadRefPatient();
      $patient->_receiver = $receiver;
    }
    else {
      $patient = $object;
    }

    if (!$receiver->isMessageSupported("CHPrimXMLEnregistrementPatient")) {
      return false;
    }

    if (!$patient->_IPP) {
      // Génération de l'IPP dans le cas de la création, ce dernier n'était pas créé
      if ($msg = $patient->generateIPP()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
      }

      $IPP = new CIdSante400();
      $IPP->loadLatestFor($patient, $receiver->_tag_patient);
      $patient->_IPP = $IPP->id400;
    }

    // Envoi pas les patients qui n'ont pas d'IPP
    if (!$receiver->_configs["send_all_patients"] && !$patient->_IPP) {
      return false;
    }

    // Si receiver est pour AppFine on ne fait pas la suite
    if ($receiver->_configs["send_appFine"]) {
      return false;
    }

    $this->sendEvenementPatient("CHPrimXMLEnregistrementPatient", $patient);

    if ($receiver->_configs["send_insured_without_admit"]) {
      if (!$receiver->isMessageSupported("CHPrimXMLDebiteursVenue")) {
        return false;
      }

      $sejour = new CSejour();
      $where = array();
      $where["patient_id"] = "= '$patient->_id'";
      $where["group_id"]   = "= '$receiver->group_id'";

      $datetime = CMbDT::dateTime();
      $where["sortie"]    = ">= '$datetime'";

      /** @var CSejour[] $sejours */
      $sejours = $sejour->loadList($where);

      // On va transmettre les informations sur le débiteur pour le séjour en cours, et ceux à venir
      foreach ($sejours as $_sejour) {
        if (!$patient->code_regime) {
          continue;
        }

        $_sejour->_receiver = $receiver;
        $_sejour->loadLastLog();

        $_sejour->loadRefPatient();

        if (!$_sejour->_NDA) {
          // Génération du NDA dans le cas de la création, ce dernier n'était pas créé
          if ($msg = $_sejour->generateNDA()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
          }

          $NDA = new CIdSante400();
          $NDA->loadLatestFor($_sejour, $receiver->_tag_sejour);
          $sejour->_NDA = $NDA->id400;
        }

        if ($receiver->isMessageSupported("CHPrimXMLDebiteursVenue")) {
          $this->sendEvenementPatient("CHPrimXMLDebiteursVenue", $_sejour);
        }
      }
    }

    $patient->_IPP = null;

    return true;
  }

  /**
   * @inheritdoc
   */
  function onBeforeMerge(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterMerge(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    $patient = $object;
    $patient->check();
    $patient->updateFormFields();
    
    $receiver = $object->_receiver;

    // Si receiver est pour AppFine on ne fait pas la suite
    if ($receiver->_configs["send_appFine"]) {
      return false;
    }
    
    foreach ($object->_fusion as $group_id => $infos_fus) {
      if ($receiver->group_id != $group_id) {
        continue;
      }

      /** @var CInteropSender $sender */
      $sender = $object->_eai_sender_guid ? CMbObject::loadFromGuid($object->_eai_sender_guid) : null;
      if ($sender && $sender->group_id == $receiver->group_id) {
        continue;
      }

      $patient1_ipp = $patient->_IPP = $infos_fus["patient1_ipp"];

      $patient_eliminee = $infos_fus["patientElimine"];
      $patient2_ipp = $patient_eliminee->_IPP = $infos_fus["patient2_ipp"];

      // Cas 0 IPP : Aucune notification envoyée
      if (!$patient1_ipp && !$patient2_ipp) {
        continue;
      }

      // Cas 1 IPP : Pas de message de fusion mais d'une modification du patient
      if ((!$patient1_ipp && $patient2_ipp) || ($patient1_ipp && !$patient2_ipp)) {
        if ($patient2_ipp) {
          $patient->_IPP = $patient2_ipp;
        }

        $this->sendEvenementPatient("CHPrimXMLEnregistrementPatient", $patient);
        continue;
      }

      // Cas 2 IPPs : Message de fusion
      if ($patient1_ipp && $patient2_ipp) {
        $patient->_patient_elimine = $patient_eliminee;

        $this->sendEvenementPatient("CHPrimXMLFusionPatient", $patient);
        continue;
      }
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    return true;
  }
}