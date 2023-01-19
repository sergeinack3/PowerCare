<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Galaxie\CGalaxie;

/**
 * Class CHL7v2SegmentZTG
 * ZFV - Represents an HL7 ZTG message segment (TAMM-GALAXIE)
 */
class CHL7v2SegmentZTG extends CHL7v2Segment
{
    /** @var string */
    public $name = "ZTG";

    /** @var CConsultation */
    public $appointment;

    /**
     * Build ZTG segment
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function build(CHEvent $event, $name = null)
    {
        parent::build($event);

        $appointment = $this->appointment;
        /** @var CReceiverHL7v2 $receiver */
        $receiver = $event->_receiver;

        if (CModule::getActive("galaxie")) {
            $galaxie = new CGalaxie();
            return $this->fill($galaxie->generateSegmentZTG($appointment, $receiver->group_id));
        }
    }
}
