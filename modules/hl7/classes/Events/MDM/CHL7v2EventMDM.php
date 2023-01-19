<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\MDM;

use CHL7v2Exception;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentMSH;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentOBR;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentOBX;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPID;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPV1;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Classe CHL7v2EventMDM
 * Medical Records/Information Management
 */
class CHL7v2EventMDM extends CHL7v2Event implements CHL7EventMDM
{
    /** @var string */
    public $event_type = "MDM";

    /**
     * Construct
     *
     * @param string $i18n i18n
     */
    function __construct($i18n = null)
    {
        $this->profil    = "MDM";
        $this->msg_codes = [
            [
                $this->event_type,
                $this->code,
                "{$this->event_type}_{$this->struct_code}",
            ],
        ];
    }

    /**
     * Build event
     *
     * @param CMbObject $object Object
     *
     * @return void
     * @see parent::build()
     *
     */
    function build($object)
    {
        parent::build($object);

        // Message Header
        $this->addMSH();

        $target = $object->loadTargetObject();

        $sejour           = null;
        $patient          = null;
        $reference_object = null;
        switch ($target->_class) {
            case "COperation":
                /** @var COperation $operation */
                $operation = $target;
                $target    = $operation->loadRefSejour();
                $patient   = $operation->loadRefPatient();
                $operation->_ref_sejour->loadRefPraticien();
                if (CModule::getActive('oxSIHCabinet')) {
                    $reference_object = $operation;
                    $sejour           = $target;
                }
                break;

            case "CSejour":
                /** @var CSejour $sejour */
                $sejour  = $target;
                $patient = $sejour->loadRefPatient();
                $sejour->loadRefPraticien();

                break;

            case "CPatient":
                $patient = $target;

                break;

            case "CConsultAnesth":
                /** @var CConsultAnesth $consult_anesth */
                $consult_anesth = $target;

                $target = $consult_anesth->loadRefConsultation();
                $target->loadRefPraticien();
                $patient = $consult_anesth->loadRefPatient();

                break;

            case "CConsultation":
                /** @var CConsultation $consultation */
                $consultation = $target;
                $patient      = $consultation->loadRefPatient();
                $consultation->loadRefPraticien();

                break;
        }

        $this->addPID($patient, $sejour);
        if ($sejour || $reference_object) {
            $this->addPV1($sejour, 1, $reference_object);
        }
        $this->addOBR($target, 1, $object);
        $this->addOBX($target, $object);
    }

    /**
     * MSH - Represents an HL7 MSH message segment (Message Header)
     *
     * @return void
     */
    private function addMSH(): void
    {
        /** @var CHL7v2SegmentMSH $MSH */
        $MSH = CHL7v2Segment::create("MSH", $this->message);
        $MSH->build($this);
    }

    /**
     * Represents an HL7 PID message segment (Patient Identification)
     *
     * @param CPatient $patient Patient
     * @param CSejour  $sejour  Admit
     *
     * @return void
     */
    private function addPID(CPatient $patient, CSejour $sejour = null): void
    {
        $segment_name = $this->_is_i18n ? "PID_FR" : "PID";

        /** @var CHL7v2SegmentPID $PID */
        $PID          = CHL7v2Segment::create($segment_name, $this->message);
        $PID->patient = $patient;
        $PID->sejour  = $sejour;
        $PID->set_id  = 1;
        $PID->build($this);
    }

    /**
     * Represents an HL7 PV1 message segment (Patient Visit)
     *
     * @param CSejour   $sejour           Admit
     * @param int       $set_id           Set ID
     * @param CMbObject $reference_object Reference object
     *
     * @return void
     */
    private function addPV1(CSejour $sejour, int $set_id = 1, CMbObject $reference_object = null): void
    {
        $segment_name = $this->_is_i18n ? "PV1_FR" : "PV1";

        /** @var CHL7v2SegmentPV1 $PV1 */
        $PV1                   = CHL7v2Segment::create($segment_name, $this->message);
        $PV1->sejour           = $sejour;
        $PV1->set_id           = $set_id;
        $PV1->curr_affectation = $sejour->_ref_hl7_affectation;
        if ($reference_object) {
            $PV1->reference_object = $reference_object;
        }
        $PV1->build($this);
    }

    /**
     * Represents an HL7 OBR message segment (Observation Request)
     *
     * @param CMbObject     $object  object
     * @param int           $set_id  Set ID
     * @param CDocumentItem $docItem Doc item
     *
     * @return void
     */
    private function addOBR(CMbObject $object, int $set_id = 1, ?CDocumentItem $docItem = null): void
    {
        /** @var CHL7v2SegmentOBR $OBR */
        $OBR          = CHL7v2Segment::create("OBR", $this->message);
        $OBR->object  = $object;
        $OBR->set_id  = $set_id;
        $OBR->docItem = $docItem;

        if ($object instanceof CSejour || $object instanceof CConsultation) {
            $ordering_provider = $object->loadRefPraticien();
        } elseif ($object instanceof CDocumentItem) {
            $ordering_provider = $object->loadRefAuthor();
        } else {
            $ordering_provider = $object;
        }
        $OBR->ordering_provider = $ordering_provider;

        $OBR->build($this);
    }


    /**
     * OBX - Represents an HL7 OBX message segment
     *
     * @param CMbObject     $object  object
     * @param CDocumentItem $docItem Document (CFile|CCompteRendu)
     *
     * @return void
     */
    private function addOBX(CMbObject $object, CDocumentItem $docItem): void
    {
        /** @var CHL7v2SegmentOBX $OBX */
        $OBX          = CHL7v2Segment::create("OBX", $this->message);
        $OBX->object  = $object;
        $OBX->docItem = $docItem;
        $OBX->build($this);
    }
}
