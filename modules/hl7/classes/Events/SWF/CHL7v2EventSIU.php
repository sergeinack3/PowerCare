<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SWF;

use Ox\AppFine\Server\CEvenementMedical;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentAIG;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentAIL;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentNTE;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentRGS;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentSCH;
use Ox\Interop\Ihe\CIHE;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Classe CHL7v2EventSIU
 * Scheduled Workflow
 */
class CHL7v2EventSIU extends CHL7v2Event implements CHL7EventSIU
{

    /** @var string */
    public $event_type = "SIU";

    /**
     * Construct
     *
     * @return CHL7v2EventSIU
     */
    function __construct()
    {
        parent::__construct();

        $this->profil      = "SWF";
        $this->msg_codes   = [
            [
                $this->event_type,
                $this->code,
                "{$this->event_type}_{$this->code}",
            ],
        ];
        $this->transaction = CIHE::getSWFTransaction($this->code);
    }

    /**
     * @see parent::build()
     */
    function build($object)
    {
        parent::build($object);

        // Message Header
        $this->addMSH();
    }

    /**
     * MSH - Represents an HL7 MSH message segment (Message Header)
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function addMSH()
    {
        $MSH = CHL7v2Segment::create("MSH", $this->message);
        $MSH->build($this);
    }

    /**
     * SCH - Represents an HL7 SCH segment (Scheduling Activity Information)
     *
     * @param CMbObject $appointment Appointment
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function addSCH(CMbObject $appointment)
    {
        /** @var CHL7v2SegmentSCH $SCH */
        $SCH              = CHL7v2Segment::create("SCH", $this->message);
        $SCH->appointment = $appointment;
        $SCH->build($this);
    }

    /**
     * Add notes and comments
     *
     * @param CMbObject $appointment
     *
     * @return void
     */
    public function addNTEs(CMbObject $appointment): void
    {
        $set_id = 1;

        // Notes et commentaires du praticien
        if ($appointment instanceof CConsultation) {
            if ($appointment->motif) {
                $this->addNTE(
                    $appointment,
                    $set_id++,
                    $appointment->motif,
                    '1R'
                );
            }

            if ($appointment->rques) {
                $this->addNTE(
                    $appointment,
                    $set_id++,
                    $appointment->rques,
                    'RE'
                );
            }

            if ($appointment->histoire_maladie) {
                $this->addNTE(
                    $appointment,
                    $set_id++,
                    $appointment->histoire_maladie,
                    'HD'
                );
            }

            if ($appointment->examen) {
                $this->addNTE(
                    $appointment,
                    $set_id++,
                    $appointment->examen,
                    'CE'
                );
            }

            if ($appointment->conclusion) {
                $this->addNTE(
                    $appointment,
                    $set_id++,
                    $appointment->conclusion,
                    'GR'
                );
            }
        }

        // Notes liées à AF
        if (CModule::getActive("appFine") && $appointment instanceof CEvenementMedical) {
            if ($appointment->remarques) {
                $date = CMbDT::transform(null, null, "%d/%m/%Y");

                $this->addNTE(
                    $appointment,
                    $set_id++,
                    "Remarque du patient - le $date :" . $appointment->remarques,
                    'PI'
                );
            }

            // Ajout d'un segment NTE pour passer l'info comme quoi le patient veut être appelé en cas de désistement
            if ($appointment->if_disclaimer) {
                $this->addNTE(
                    $appointment,
                    $set_id++,
                    CAppUI::tr('CAppFine-msg-Appointment withdrawal'),
                    'WITHDRAWAL'
                );
            }

            if ($appointment->cancel_reason && $appointment->cancel) {
              $this->addNTE(
                $appointment,
                $set_id++,
                "Remarque du patient sur l'annulation du rendez-vous - le $date :" . $appointment->cancel_reason,
                'PI'
              );
            }
        }
    }

    /**
     * NTE - Represents an HL7 NTE Segment (Notes and comments)
     *
     * @param CMbObject $appointment Appointment
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function addNTE(CMbObject $appointment, int $set_id = 1, string $comment = null, string $comment_type = null)
    {
        /** @var CHL7v2SegmentNTE $NTE */
        $NTE               = CHL7v2Segment::create("NTE", $this->message);
        $NTE->appointment  = $appointment;
        $NTE->set_id       = $set_id;
        $NTE->comment      = $comment;
        $NTE->comment_type = $comment_type;
        $NTE->build($this);
    }

    /**
     * Represents an HL7 PID message segment (Patient Identification)
     *
     * @param CPatient $patient Patient
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function addPID(CPatient $patient)
    {
        $PID          = CHL7v2Segment::create("PID", $this->message);
        $PID->patient = $patient;
        $PID->set_id  = 1;
        $PID->build($this);
    }

    /**
     * Represents an HL7 PD1 message segment (Patient Additional Demographic)
     *
     * @param CPatient $patient Patient
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function addPD1(CPatient $patient)
    {
        $PD1          = CHL7v2Segment::create("PD1", $this->message);
        $PD1->patient = $patient;
        $PD1->build($this);
    }

    /**
     * Represents an HL7 PV1 message segment (Patient Visit)
     *
     * @param CSejour $sejour Admit
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function addPV1(CSejour $sejour)
    {
        $PV1         = CHL7v2Segment::create("PV1", $this->message);
        $PV1->sejour = $sejour;
        $PV1->build($this);
    }

    /**
     * RGS - Represents an HL7 SCH segment (Resource Group)
     *
     * @param CConsultation $appointment Appointment
     * @param int           $set_id      Set ID
     *
     * @return void
     */
    function addRGS(CConsultation $appointment, $set_id = 1)
    {
        /** @var CHL7v2SegmentRGS $RGS */
        $RGS              = CHL7v2Segment::create("RGS", $this->message);
        $RGS->set_id      = $set_id;
        $RGS->appointment = $appointment;
        $RGS->build($this);
    }

    /**
     * AIG - Represents an HL7 SCH segment (Appointment Information - General Resource)
     *
     * @param CConsultation $appointment Appointment
     * @param int           $set_id      Set ID
     *
     * @return void
     */
    function addAIG(CConsultation $appointment, $set_id = 1)
    {
        /** @var CHL7v2SegmentAIG $AIG */
        $AIG              = CHL7v2Segment::create("AIG", $this->message);
        $AIG->set_id      = $set_id;
        $AIG->appointment = $appointment;
        $AIG->build($this);
    }

    /**
     * AIL - Represents an HL7 AIL segment (Appointment Information - Location Resource)
     *
     * @param CMbObject $appointment Appointment
     * @param int       $set_id      Set ID
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function addAIL(CMbObject $appointment, $set_id = 1)
    {
        /** @var CHL7v2SegmentAIL $AIL */
        $AIL              = CHL7v2Segment::create("AIL", $this->message);
        $AIL->set_id      = $set_id;
        $AIL->appointment = $appointment;
        $AIL->build($this);
    }
}
