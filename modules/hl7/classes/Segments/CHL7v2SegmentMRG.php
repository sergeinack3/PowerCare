<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Client\CReceiverHL7v2AppFine;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Doctolib\CReceiverHL7v2Doctolib;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentMRG
 * MRG - Represents an HL7 MRG message segment (Merge Patient Information)
 */
class CHL7v2SegmentMRG extends CHL7v2Segment
{

    /** @var string */
    public $name = "MRG";


    /** @var CPatient */
    public $deleted_object;

    /**
     * Build MRG segement
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return null
     * @throws CHL7v2Exception
     */
    function build(CHEvent $event, $name = null)
    {
        parent::build($event);

        $receiver       = $event->_receiver;
        $deleted_object = $this->deleted_object;
        $group          = $receiver->_ref_group;

        $data = [];

        if ($deleted_object instanceof CSejour) {
            $sejour  = $deleted_object;
            $patient = $sejour->loadRefPatient();
            $mrg5    = $sejour->_NDA;
        } else {
            $patient = $deleted_object;
            $mrg5    = null;
        }

        // MRG-1: Prior Patient Identifier List (CX) (repeating)
        if (CModule::getActive('doctolib') && $receiver && $receiver instanceof CReceiverHL7v2Doctolib) {
            $data[] = CReceiverHL7v2Doctolib::getPersonIdentifiers($patient, $group, $receiver, true);
        } elseif (
            CModule::getActive('appFineClient') && $receiver && $receiver->_configs['send_evenement_to_mbdmp']
            && CAppFineClient::loadIdex($patient, $receiver->group_id)
        ) {
            $data[] = CReceiverHL7v2AppFine::getPersonIdentifiers($patient, $group, $receiver);
        } elseif (isset($patient->_disable_insi_identity_source)) {
            // On inactive la source INSi, on va donc supprimer l'identifiant INS-NIR
            $data[] = $this->getOldINSIdentifier($patient, $patient->_disable_insi_identity_source);
        } else {
            // On change l'IPP d'un patient
            $data[] = $this->getPersonIdentifiers($patient, $group, $receiver);
        }

        // MRG-2: Prior Alternate Patient ID (CX) (optional repeating)
        $data[] = null;

        // MRG-3: Prior Patient Account Number (CX) (optional)
        $data[] = null;

        // MRG-4: Prior Patient ID (CX) (optional)
        $data[] = null;

        // MRG-5: Prior Visit Number (CX) (optional)
        $data[] = $mrg5;

        // MRG-6: Prior Alternate Visit ID (CX) (optional)
        $data[] = null;

        // MRG-7: Prior Patient Name (XPN) (optional repeating)
        $data[] = $this->getXPN($patient, $receiver);

        $this->fill($data);
    }

    /**
     * Fill other identifiers
     *
     * @param array         &$identifiers Identifiers
     * @param CPatient       $patient     Person
     * @param CInteropActor  $actor       Interop actor
     *
     * @return null
     * @throws CHL7v2Exception
     */
    function fillOtherIdentifiers(&$identifiers, CPatient $patient, CInteropActor $actor = null)
    {
        if ($actor->_configs["send_own_identifier"]) {
            $identifiers[] = [
                $patient->_id,
                null,
                null,
                // PID-3-4 Autorité d'affectation
                $this->getAssigningAuthority("mediboard", null, null, null, $actor->group_id),
                $actor->_configs["build_identifier_authority"] == "PI_AN" ? "PI" : "RI",
            ];
        }
    }
}
