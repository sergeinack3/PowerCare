<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\DEC;

use Ox\AppFine\Server\CEvenementMedical;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentOBR;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentOBX;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPID;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPV1;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Class CHL7v2EventORUR01
 * R01 - Observation results reports for the patient
 */
class CHL7v2EventORUR01 extends CHL7v2EventDEC implements CHL7EventORUR01
{
    /** @var string */
    public $event_type = "ORU";

    /** @var string[][] */
    public static $values_type_parameters = [
        "ED" => [
            "id_file" => "text",
        ],

        "RP" => [
            "id_file"   => "text",
            "link_file" => "text",
        ],
    ];

    /** @var string */
    public $code = "R01";

    /** @var string */
    public $struct_code = "R01";

    /**
     * Construct
     *
     * @param string $i18n i18n
     *
     * @return CHL7v2EventORUR01
     */
    public function __construct($i18n = null)
    {
        $this->msg_codes = [
            [
                $this->event_type,
                $this->code,
            ],
        ];

        parent::__construct($i18n);
    }

    /**
     * Build R01 event
     *
     * @param CMbObject $object object
     *
     * @return void
     * @see parent::build()
     *
     */
    public function build($object)
    {
        /** @var CDocumentItem $object */
        parent::build($object);

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
                    $sejour = $target;
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

            case "CPrescription":
                if (CModule::getActive("appFineClient") && $target instanceof CPrescription) {
                    $patient = $target->loadRefPatient();
                    $target  = $target->loadRefObject();
                }
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

            case "CEvenementMedical":
                if (CModule::getActive("appFine") && $target instanceof CEvenementMedical) {
                    /** @var CEvenementMedical $event_medical */
                    $event_medical = $target;
                    $patient       = $event_medical->loadRefPatient();
                }

                break;

            case "CEvenementPatient":
                if (CModule::getActive("oxCabinetSIH") && $target instanceof CEvenementPatient) {
                    /** @var CEvenementPatient $event_medical */
                    $event_medical = $target;
                    $patient       = $event_medical->loadRefPatient();

                    $sejour = new CSejour();
                    $reference_object = $event_medical;
                }

                break;

            case "CRGPDConsent":
                if (CModule::getActive("appFine") && $target instanceof CRGPDConsent) {
                    /** @var CRGPDConsent $rgpd_consent */
                    $rgpd_consent = $target;
                    // Tout le temps un patient parce qu'on génère un ORU que pour les fichiers RGPD.txt
                    // rattachés à un patient
                    $patient = $rgpd_consent->loadTargetObject();
                    $target  = $patient;
                }
                break;

            default:
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
    public function addOBR(CMbObject $object, int $set_id = 1, ?CDocumentItem $docItem = null): void
    {
        /** @var CHL7v2SegmentOBR $OBR */
        $OBR         = CHL7v2Segment::create("OBR", $this->message);
        $OBR->object = $object;
        $OBR->set_id = $set_id;
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
