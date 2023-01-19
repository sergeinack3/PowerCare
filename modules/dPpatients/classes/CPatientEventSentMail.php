<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;

/**
 * Class CPatientEventSentMail
 * Each mail (email or postal) sent by the practitioner to the patient is saved using this model
 * Mails are attached to a patient event
 * e.g. send a mail to make a new appointment after a surgery
 */
class CPatientEventSentMail extends CMbObject {
  public $patientevent_sent_mail_id;
  public $patient_event_id;
  public $datetime;
  public $type;

  /**
   * @inheritDoc
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'patientevent_sent_mail';
    $spec->key   = 'patientevent_sent_mail_id';

    return $spec;
  }

  /**
   * @inheritDoc
   */
  public function getProps() {
    $props                     = parent::getProps();
    $props['patient_event_id'] = 'ref class|CEvenementPatient back|event_sent_mail';
    $props['datetime']         = 'dateTime default|now';
    $props['type']             = 'enum list|postal|email';

    return $props;
  }
}