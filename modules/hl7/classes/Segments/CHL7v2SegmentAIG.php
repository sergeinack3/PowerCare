<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Doctolib\CReceiverHL7v2Doctolib;

/**
 * Class CHL7v2SegmentAIG
 * AIG - Represents an HL7 AIG message segment (Appointment Information - General Resource)
 */
class CHL7v2SegmentAIG extends CHL7v2Segment
{

    /** @var string */
    public $name = "AIG";

    /** @var null */
    public $set_id;


    /** @var CConsultation */
    public $appointment;

    /**
     * Build AIG segement
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

        $appointment = $this->appointment;
        $receiver    = $event->_receiver;

        $praticien = $appointment->_ref_praticien;
        $praticien->completeField("adeli", "rpps");

        $data = [];

        // AIG-1: Set ID - AIG (SI)
        $data[] = $this->set_id;

        if (CModule::getActive('doctolib') && $receiver && $receiver instanceof CReceiverHL7v2Doctolib) {
            $agenda         = $appointment->loadRefPlageConsult()->loadRefAgendaPraticien();
            $data_praticien = [
                [
                    $agenda->_guid,
                    $praticien->_user_last_name . ' ' . $praticien->_user_first_name,
                ],
            ];

            $data[] = null;
            $data[] = null;

            // AIG-4: Resource Type (CE)
            $data[] = $data_praticien;

            $this->fill($data);

            return;
        }

        // AIG-2: Segment Action Code (ID) (optional)
        $data[] = $this->getSegmentActionCode($event);

        // AIG-3: Resource ID (CE) (optional)
        $xcn1           = CValue::first($praticien->rpps, $praticien->adeli, $praticien->_id);
        $xcn2           = $praticien->_user_last_name;
        $xcn3           = $praticien->_user_first_name;
        $xcn13          = ($praticien->rpps ? "RPPS" : ($praticien->adeli ? "ADELI" : "RI"));
        $data_praticien = [
            [
                $xcn1,
                $xcn13,
                null,
                $praticien->_id,
                "$xcn2 $xcn3",
            ],
        ];
        $data[]         = $data_praticien;

        // AIG-4: Resource Type (CE)
        $data[] = $data_praticien;

        // AIG-5: Resource Group (CE) (optional repeating)
        $schedule = $appointment->loadRefPlageConsult();
        $function = $schedule->function_id ? $schedule->loadRefFunction() : $praticien->loadRefFunction();
        $group    = $function->loadRefGroup();
        $data[]   = [
            [
                $function->finess ?: $group->finess,
                "FINESS",
                null,
                $function->_id,
                $function->_view,
            ],
        ];

        // AIG-6: Resource Quantity (NM) (optional)
        $data[] = null;

        // AIG-7: Resource Quantity Units (CE) (optional)
        $data[] = null;

        // AIG-8: Start Date/Time (TS) (optional)
        $data[] = null;

        // AIG-9: Start Date/Time Offset (NM) (optional)
        $data[] = null;

        // AIG-10: Start Date/Time Offset Units (CE) (optional)
        $data[] = null;

        // AIG-11: Duration (NM) (optional)
        $data[] = null;

        // AIG-12: Duration Units (CE) (optional)
        $data[] = null;

        // AIG-13: Allow Substitution Code (IS) (optional)
        $data[] = null;

        // AIG-14: Filler Status Code (CE) (optional)
        $data[] = $this->getFillerStatutsCode($appointment);

        $this->fill($data);
    }
} 
