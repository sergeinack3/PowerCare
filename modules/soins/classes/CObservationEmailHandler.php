<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\System\CSourceSMTP;
use phpmailerException;

/**
 * Handler sur les Observations Medicales
 */
class CObservationEmailHandler extends ObjectHandler {
  static $handled = array("CObservationMedicale", "CTransmissionMedicale");

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    if (!CModule::getActive("soins")) {
      return null;
    }

    return in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    /* @var CObservationMedicale|CTransmissionMedicale $object */
    if (!$this->isHandled($object)) {
      return;
    }
    if ($object instanceof CTransmissionMedicale && !$object->dietetique) {
      return;
    }
    if ($object instanceof CObservationMedicale && $object->etiquette !== 'dietetique') {
      return;
    }
    if ($object instanceof CTransmissionMedicale && $object->type == "data") {
      return;
    }
    $email_send_modifs = CAppUI::gconf("soins Transmissions email_send_modifs");
    if (!$email_send_modifs) {
      return;
    }
    if (!preg_match("/^[-a-z0-9\._]+@[-a-z0-9\.]+\.[a-z]{2,4}$/i", $email_send_modifs)) {
      CAppUI::stepAjax("Le format de l'email n'est pas valide", UI_MSG_ERROR);

      return;
    }
    $email_send_modifs = CAppUI::gconf("soins Transmissions email_send_modifs");
    /** @var $exchange_source CSourceSMTP */
    $exchange_source         = new CSourceSMTP();
    $exchange_source->name   = 'system-message';
    $exchange_source->active = 1;
    $exchange_source->loadMatchingObject();

    if (!$exchange_source->_id) {
      CAppUI::stepAjax("CExchangeSource.none", UI_MSG_ERROR);
    }

    try {
      $sejour  = $object->loadRefSejour();
      $patient = $sejour->loadRelPatient();

      $body = "";
      $body .= CAppUI::tr("User") . " : " . $object->loadRefUser()->_view . "<br/>";
      $body .= CAppUI::tr("CAffectation.current") . " : " . $sejour->loadRefCurrAffectation()->_view . "<br/>";
      $body .= CAppUI::tr("Date") . " : " . CMbDT::format($object->date, CAppUI::conf('datetime')) . "<br/>";
      $body .= CAppUI::tr($object->_class . "-degre") . " : " . CAppUI::tr($object->_class . ".degre." . $object->degre) . "<br/>";
      if ($object->cancellation_date) {
        $body .= CAppUI::tr($object->_class . "-cancellation_date") . " : ";
        $body .= CMbDT::format($object->cancellation_date, CAppUI::conf('datetime')) . "<br/>";
      }

      if ($object instanceof CObservationMedicale) {
        $body .= CAppUI::tr($object->_class . "-type") . " : " . CAppUI::tr($object->_class . ".type." . $object->type) . "<br/>";
        $body .= CAppUI::tr($object->_class) . ": " . nl2br($object->text);
      }
      else {
        if ($object->date_max) {
          $body .= CAppUI::tr($object->_class . "-date_max") . " : ";
          $body .= CMbDT::format($object->date_max, CAppUI::conf('datetime')) . "<br/>";
        }
        if ($object->cible_id) {
          $cible      = $object->loadRefCible();
          $cible_view = $cible->libelle_ATC ?: $cible->loadTargetObject()->_view;
          $body       .= CAppUI::tr($object->_class . "-cible_id") . " : " . $cible_view . "<br/>";
        }
        if ($object->locked) {
          $body .= CAppUI::tr($object->_class . "-locked") . "<br/>";
        }
        $object->loadRefsTransmissionsSibling();

        foreach ($object->_trans_sibling as $type_trans => $_transmission) {
          if ($object->type != "action" || $type_trans != "result") {
            $body .= CAppUI::tr($object->_class . ".type." . $type_trans) . " : " . nl2br($_transmission->text) . "<br/>";
          }
        }
        $body .= CAppUI::tr($object->_class . ".type." . $object->type) . " : " . nl2br($object->text);
      }

      $exchange_source->init();
      $exchange_source->addTo($email_send_modifs);
      $exchange_source->setSubject(CAppUI::tr($object->_class) . " diététique pour le patient " . $patient->_view);
      $exchange_source->setBody($body);
      $exchange_source->send();
      CAppUI::setMsg('common-msg-Notification send', UI_MSG_OK);
    }
    catch (phpmailerException $e) {
      trigger_error($e->getMessage(), E_USER_WARNING);
    }
    catch (CMbException $e) {
      trigger_error($e->getMessage(), E_USER_WARNING);
    }
  }

  /**
   * @inheritdoc
   */
  function onAfterMerge(CStoredObject $object) {
    $this->onAfterStore($object);
  }
}
