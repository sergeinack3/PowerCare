<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Notifications\CNotificationEvent;

/**
 * Categories des evenements patients
 */
class CTypeEvenementPatient extends CMbObject {
  // DB Table key
  public $type_evenement_patient_id;

  // DB fields
  public $function_id;
  public $libelle;
  public $notification;
  public $mailing_model_id;

  public $_notification_days;
  public $_notification_text_model;

  public $_store_notification = false;

  /** @var CFunctions */
  public $_ref_function;

  /** @var CEvenementPatient[] */
  public $_ref_evenements_patient;

  /** @var CNotificationEvent */
  public $_ref_notification;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'type_evenement_patient';
    $spec->key   = 'type_evenement_patient_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["function_id"]  = "ref class|CFunctions back|type_evenements";
    $props["libelle"]      = "str notNull";
    $props['notification'] = 'bool default|0';
    $props['mailing_model_id'] = 'ref class|CCompteRendu back|mailing_event_type';

    $props['_notification_days']       = 'num min|0 max|30';
    $props['_notification_text_model'] = 'text';

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->libelle;

    if ($this->notification && CModule::getActive('notifications')) {
      $this->loadRefNotification();
      if ($this->_ref_notification->_id && !$this->_store_notification) {
        $this->_notification_days       = $this->_ref_notification->days;
        $this->_notification_text_model = html_entity_decode($this->_ref_notification->text_model, ENT_QUOTES, 'iso-8859-1');;
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function store() {
    if ($msg = parent::store()) {
      return $msg;
    }

    $this->loadRefNotification();

    /* Création ou modification de la notification */
    if ($this->notification && CModule::getActive('notifications')) {
      $notification = $this->_ref_notification;
      if (!$notification->_id) {
          $notification->context_class = $this->_class;
          $notification->object_id     = $this->_id;
          $notification->function_id   = $this->function_id;
          $notification->type          = 'sms';
          $notification->moment        = 'before';
          $notification->group_id      = CGroups::loadCurrent()->_id;
      }

      $notification->active     = '1';
      $notification->days       = $this->_notification_days;
      $notification->text_model = $this->_notification_text_model;

      $msg = $notification->store();

      return $msg;
    }
    /* Désactivation de la notification si une notification est liée au type */
    elseif (CModule::getActive('notifications') && $this->_ref_notification->_id) {
      $this->_ref_notification->active = '0';
      $msg                             = $this->_ref_notification->store();

      return $msg;
    }
  }

  /**
   * Charge fonction liée
   *
   * @return CFunctions
   * @throws \Exception
   */
  function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id");
  }

  /**
   * Charge les évènements du type
   *
   * @return CEvenementPatient[]
   * @throws \Exception
   */
  function loadRefEvenementsPatient() {
    return $this->_ref_evenements_patient = $this->loadBackRefs("evenements_patient");
  }

  /**
   * Load the CNotificationEvent
   *
   * @return CNotificationEvent
   * @throws \Exception
   */
  function loadRefNotification() {
    if (CModule::getActive('notifications')) {
      $this->_ref_notification = $this->loadUniqueBackRef('notification_events');
    }

    return $this->_ref_notification;
  }

  /**
   * @inheritDoc
   */
  public function getPerm($permType)
  {
      return $this->function_id ? ($this->loadRefFunction()->getPerm($permType)) : parent::getPerm($permType);
  }
}

