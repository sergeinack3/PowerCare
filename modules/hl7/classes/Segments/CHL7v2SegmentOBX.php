<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Client\CAppFineClientOrderItem;
use Ox\AppFine\Server\CAppFineServer;
use Ox\AppFine\Server\CEvenementMedical;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Sas\CSAS;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHL7v2SegmentOBX
 * OBX - Represents an HL7 OBX message segment (Complément d'information sur la venue)
 */
class CHL7v2SegmentOBX extends CHL7v2Segment
{
    /** @var string */
    public $name = "OBX";
    /** @var int */
    public $set_id;
    /** @var string */
    public $value_type;
    /** @var array */
    public $observation_identifier;
    /** @var string */
    public $observation_value;
    /** @var string */
    public $observation_datetime;
    /** @var string */
    public $unit;
    /** @var CMbObject */
    public $object;
    /** @var CDocumentItem */
    public $docItem;

    /**
     * Build OBX segement
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

        $object   = $this->object;
        $receiver = $event->_receiver;

        if (CModule::getActive("appFineClient") && $object instanceof CAppFineClientOrderItem) {
            return $this->fill(CAppFineClient::generateSegmentOBX($object, $event->_receiver));
        }

        // OBX-1: Set ID - OBX (SI) (optional)
        $data[] = $this->set_id;

        // OBX-2: Value Type (ID)
        $value_type = CMbArray::get($receiver->_configs, "build_OBX_2");
        if ($this->docItem) {
            if (!$value_type) {
                $value_type = "ED";
            }
        } else {
            $value_type = $this->value_type;
        }
        $data[] = $value_type;

        // OBX-3: Observation Identifier (CE)
        $file_category = null;
        if ($this->docItem && $receiver->_configs['build_OBX_3'] == 'DMP' && CModule::getActive('dmp')) {
            $data[] = $this->getValueTypeDMP($this->docItem);
        } elseif ($this->docItem && CModule::getActive(
                'appFineClient'
            ) && $receiver->_configs['send_evenement_to_mbdmp']) {
            $data[] = $object->_guid;
        } elseif ($this->docItem) {
            $file_category = $this->docItem->loadRefCategory();
            $data[]        = [
                [
                    $file_category->_id,
                    $file_category->nom,
                ],
            ];
        } else {
            $data[] = $this->observation_identifier ?: $object->_guid;
        }

        // OBX-4: Observation Sub-ID (ST) (optional)
        // Configuration d'un idex sur la catégorie
        if ($this->docItem && $file_category) {
            $idex = CIdSante400::getMatch(
                $file_category->_class,
                CSAS::getFilesCategoryAssociationTag($receiver->group_id),
                null,
                $file_category->_id
            );
            $data[] = $idex->_id ? $idex->id400 : null;
        } else {
            $data[] = null;
        }


        // OBX-5: Observation Value (Varies) (optional repeating)
        if ($this->docItem) {
            $data[] = $this->getContent($this->docItem, $receiver);
        } else {
            $data[] = $this->observation_value;
        }

        // OBX-6: Units (CE) (optional)
        $data[] = $this->unit;

        // OBX-7: References Range (ST) (optional)
        $data[] = null;

        // OBX-8: Abnormal Flags (IS) (optional repeating)
        $data[] = null;

        // OBX-9: Probability (NM) (optional)
        $data[] = null;

        // OBX-10: Nature of Abnormal Test (ID) (optional repeating)
        $data[] = null;

        // OBX-11: Observation Result Status (ID)
        // Table  - 0085
        // D - Deletes the OBX record
        // F - Final results
        // N - Not asked
        // O - Order detail description only
        // S - Partial results
        // W - Post original as wrong
        // P - Preliminary results
        // C - Record coming over is a correction and thus replaces a final result
        // X - Results cannot be obtained for this observation
        // R - Results entered
        // U - Results status change to final without retransmitting results already sent as _preliminary
        // I - Specimen in lab
        $status = 'F';
        if ($this->docItem) {
            $current_log = $this->docItem->loadLastLog();
            if ($this->docItem->annule) {
                $status = 'D';
            } elseif ($current_log->type === 'store') {
                $status = 'C';
            }
        }
        $data[] = $status;

        // OBX-12: Effective Date of Reference Range (TS) (optional)
        $data[] = null;

        // OBX-13: User Defined Access Checks (ST) (optional)
        $data[] = null;

        // OBX-14: Date/Time of the Observation (TS) (optional)
        $data[] = $this->observation_datetime;

        // OBX-15: Producer's ID (CE) (optional)
        if ($this->docItem && CModule::getActive(
                "appFine"
            ) && ($object instanceof CEvenementMedical || $object instanceof CPatient)) {
            $data[] = CAppFineServer::generateOBX15($this->docItem, $this->object, $receiver);
        } elseif ($this->docItem && CModule::getActive(
                "appFineClient"
            ) && $event->_receiver->_configs["send_evenement_to_mbdmp"]
            && ($object instanceof CSejour || $object instanceof CConsultation)
        ) {
            $data[] = $this->docItem->_guid;
        } else {
            $data[] = null;
        }

        // OBX-16: Responsible Observer (XCN) (optional repeating)
        if ($this->docItem) {
            $data[] = $this->docItem instanceof CCompteRendu && $this->docItem->locker_id
                ? $this->getXCN($this->docItem->loadRefLocker(), $receiver)
                : null;
        } else {
            $data[] = null;
        }

        // OBX-17: Observation Method (CE) (optional repeating)
        $data[] = null;

        // OBX-18: Equipment Instance Identifier (EI) (optional repeating)
        $data[] = null;

        // OBX-19: Date/Time of the Analysis (TS) (optional)
        $data[] = null;

        $this->fill($data);
    }

    /**
     * Initialize OBX segement
     *
     * @param CMbObject $object                 Object
     * @param int       $set_id                 Set id
     * @param string    $value_type             Value type
     * @param array     $observation_identifier Observation Identifier
     * @param string    $observation_value      Observation Value
     * @param string    $unit                   Unit
     * @param string    $observation_datetime   Date/Time of the Observation
     *
     * @return void
     */
    function initializeDatas(
        CMbObject $object,
        $set_id,
        $value_type,
        $observation_identifier,
        $observation_value,
        $unit,
        $observation_datetime
    ) {
        $this->object                 = $object;
        $this->set_id                 = $set_id;
        $this->value_type             = $value_type;
        $this->observation_identifier = $observation_identifier;
        $this->observation_value      = $observation_value;
        $this->unit                   = $unit;
        $this->observation_datetime   = $observation_datetime;
    }
}
