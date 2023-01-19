<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Urgences\CRPU;

/**
 * Class CSmpHprimXMLObjectHandler
 * SMP H'XML Object handler
 */

class CSmpHprimXMLObjectHandler extends CHprimXMLObjectHandler {
  /** @var array $handled */
  static $handled = array ("CSejour", "CAffectation", "CNaissance");

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

    $current_log = $object->loadLastLog();

    // Traitement Sejour
    if ($object instanceof CSejour) {
      $sejour = $object;

      if (!$receiver->isMessageSupported("CHPrimXMLVenuePatient")) {
        return false;
      }

      // Si le group_id du séjour est différent de celui du destinataire
      if ($sejour->group_id != $receiver->group_id) {
        return false;
      }

      // On est sur le séjour relicat, on ne synchronise aucun flux
      /** @var CRPU $rpu */
      $rpu  = $sejour->loadRefRPU();
      if ($rpu && $rpu->mutation_sejour_id && ($rpu->sejour_id != $rpu->mutation_sejour_id)) {
        return false;
      }

      // Si on est en train de créer un séjour et qu'il s'agit d'une naissance
      $current_log = $sejour->loadLastLog();
      if ($current_log->type == "create" && $sejour->_naissance) {
        return false;
      }

      // Si on ne gère les séjours du bébé on ne transmet pas séjour si c'est un séjour enfant
      if (!$receiver->_configs["send_child_admit"]) {
        $naissance = new CNaissance();
        $naissance->sejour_enfant_id = $sejour->_id;
        $naissance->loadMatchingObject();
        if ($naissance->_id) {
          return false;
        }
      }

      $sejour->loadNDA();
      if (!$sejour->_NDA) {
        // Génération du NDA dans le cas de la création, ce dernier n'était pas créé
        if ($msg = $sejour->generateNDA()) {
          CAppUI::setMsg($msg, UI_MSG_ERROR);
        }

        $NDA = new CIdSante400();
        $NDA->loadLatestFor($sejour, $receiver->_tag_sejour);
        $sejour->_NDA = $NDA->id400;
      }

      $sejour->loadRefPraticien();
      $sejour->loadRefPatient();
      $sejour->loadRefAdresseParPraticien();

      if (!$sejour->_ref_patient->_IPP) {
        $IPP = new CIdSante400();
        //Paramétrage de l'id 400
        $IPP->loadLatestFor($sejour->_ref_patient, $receiver->_tag_patient);

        $sejour->_ref_patient->_IPP = $IPP->id400;
      }

      $this->sendEvenementPatient("CHPrimXMLVenuePatient", $sejour);

      if ($receiver->isMessageSupported("CHPrimXMLDebiteursVenue") && $sejour->_ref_patient->code_regime && !$sejour->annule) {
        $this->sendEvenementPatient("CHPrimXMLDebiteursVenue", $sejour);
      }

      $sejour->_NDA = null;

      return true;
    }

    // Traitement Affectation
    elseif ($object instanceof CAffectation) {
      $affectation = $object;

      if (!$current_log || $affectation->_no_synchro_eai || !in_array($current_log->type, array("create", "store"))) {
        return false;
      }

      // Cas où :
      // * on est l'initiateur du message
      // * le destinataire ne supporte pas le message
      if (!$receiver->isMessageSupported("CHPrimXMLMouvementPatient")) {
        return false;
      }

      // Affectation non liée à un séjour
      $sejour = $affectation->loadRefSejour();
      if (!$sejour->_id) {
        return false;
      }

      // Pas d'envoie d'affectation pour les séjours reliquats
      // Sauf si le séjour est en UHCD
      $rpu = $sejour->loadRefRPU();
      if ($rpu && $rpu->mutation_sejour_id && ($rpu->sejour_id != $rpu->mutation_sejour_id) && !$sejour->UHCD) {
        return false;
      }

      // Si le group_id du séjour est différent de celui du destinataire
      if ($sejour->group_id != $receiver->group_id) {
        return false;
      }

      $sejour->loadRefPatient();
      $sejour->_receiver = $receiver;

      // Envoi de l'événement
      $this->sendEvenementPatient("CHPrimXMLMouvementPatient", $affectation);

      return true;
    }

    // Traitement Naissance
    if ($object instanceof CNaissance) {
      $naissance = $object;

      if (!$receiver->isMessageSupported("CHPrimXMLVenuePatient")) {
        return false;
      }

      $sejour_enfant = $naissance->loadRefSejourEnfant();
      $sejour_enfant->loadRefPraticien();
      $sejour_enfant->loadRefPatient();
      $sejour_enfant->loadRefAdresseParPraticien();
      $sejour_enfant->loadLastLog();

      if (!$sejour_enfant->_ref_patient->_IPP) {
        $IPP = new CIdSante400();
        //Paramétrage de l'id 400
        $IPP->loadLatestFor($sejour_enfant->_ref_patient, $receiver->_tag_patient);

        $sejour_enfant->_ref_patient->_IPP = $IPP->id400;
      }

      // Si on gère les séjours du bébé on transmet le séjour !
      if ($receiver->_configs["send_child_admit"]) {
        $sejour_enfant->_receiver = $receiver;

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour_enfant->group_id != $receiver->group_id) {
          return;
        }

        if (!$sejour_enfant->_NDA) {
          // Génération du NDA dans le cas de la création, ce dernier n'était pas créé
          if ($msg = $sejour_enfant->generateNDA()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
          }

          $NDA = new CIdSante400();
          $NDA->loadLatestFor($sejour_enfant, $receiver->_tag_sejour);
          $sejour_enfant->_NDA = $NDA->id400;
        }

        // Envoi de l'événement
        $this->sendEvenementPatient("CHPrimXMLVenuePatient", $sejour_enfant);

        $sejour_enfant->_NDA = null;
      }
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onBeforeMerge(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }
  }

  /**
   * @inheritdoc
   */
  function onAfterMerge(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }
    
    // Traitement Séjour
    if ($object instanceof CSejour) {
      $sejour = $object;
      $sejour->check();
      $sejour->updateFormFields();
      $sejour->loadRefPatient();
      $sejour->loadRefPraticien();
      $sejour->loadLastLog();
      $sejour->loadRefAdresseParPraticien();
      
      $receiver = $object->_receiver;

      foreach ($object->_fusion as $group_id => $infos_fus) {
        if ($receiver->group_id != $group_id) {
          continue;
        }

        /** @var CInteropSender $sender */
        $sender = CMbObject::loadFromGuid($object->_eai_sender_guid);
        if ($sender->group_id == $receiver->group_id) {
          continue;
        }

        $sejour1_nda = $sejour->_NDA = $infos_fus["sejour1_nda"];

        $sejour_elimine = $infos_fus["sejourElimine"];
        $sejour2_nda = $sejour_elimine->_NDA = $infos_fus["sejour2_nda"];

        // Cas 0 NDA : Aucune notification envoyée
        if (!$sejour1_nda && !$sejour2_nda) {
          continue;
        }

        // Cas 1 NDA : Pas de message de fusion mais d'une modification de la venue
        if ((!$sejour1_nda && $sejour2_nda) || ($sejour1_nda && !$sejour2_nda)) {
          if ($sejour2_nda) {
            $sejour->_NDA = $sejour2_nda;
          }

          $this->sendEvenementPatient("CHPrimXMLVenuePatient", $sejour);
          continue;
        }

        // Cas 2 NDA : Message de fusion
        if ($sejour1_nda && $sejour2_nda) {
          $sejour_elimine->check();
          $sejour_elimine->updateFormFields();
          $sejour_elimine->loadRefPatient();
          $sejour_elimine->loadRefPraticien();
          $sejour_elimine->loadLastLog();
          $sejour_elimine->loadRefAdresseParPraticien();

          $sejour->_sejour_elimine = $sejour_elimine;

          $this->sendEvenementPatient("CHPrimXMLFusionVenue", $sejour);
          continue;
        }
      }
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onBeforeDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }
  }
}