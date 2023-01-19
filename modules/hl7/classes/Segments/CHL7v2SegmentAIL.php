<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Server\CAppFineServer;
use Ox\AppFine\Server\CEvenementMedical;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Doctolib\CReceiverHL7v2Doctolib;

/**
 * Class CHL7v2SegmentAIL
 * AIL - Represents an AIL ZBE message segment (Appointment Information - Location Resource)
 */

class CHL7v2SegmentAIL extends CHL7v2Segment {

  /** @var string */
  public $name = "AIL";

  /** @var null */
  public $set_id;
  

  /** @var CConsultation */
  public $appointment;

  /**
   * Build AIL segement
   *
   * @param CHL7v2Event $event Event
   * @param string      $name  Segment name
   *
   * @return null
   * @throws CHL7v2Exception
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    
    $appointment = $this->appointment;
    $receiver    = $event->_receiver;

    $data = array();
    if (CModule::getActive("appFine") && $appointment instanceof CEvenementMedical) {
        return $this->fill(CAppFineServer::generateSegmentAIL($appointment, $event));
    }

    if (CModule::getActive('doctolib') && $receiver && $receiver instanceof CReceiverHL7v2Doctolib) {
      return;
    }

      // AIL-1: Set ID - AIL (SI)
    $data[] = $this->set_id;
    
    // AIL-2: Segment Action Code (ID) (optional)
    $data[] = $this->getSegmentActionCode($event);
    
    // AIL-3: Location Resource ID (PL) (optional repeating)
    $data[] = array(
      array(
        null,
        null,
        null,
        null,
        null,
        null,
        // Building
        substr($appointment->loadRefGroup()->_view, 0, 19)
      )
    );
    
    // AIL-4: Location Type-AIL (CE) (optional)
    // P pour Phone
    $data[] = [
        [
            $appointment->teleconsultation ? 'P' : null,
            null,
        ],
    ];
    
    // AIL-5: Location Group (CE) (optional)
    $data[] = null;
    
    // AIL-6: Start Date/Time (TS) (optional)
    $data[] = null;
    
    // AIL-7: Start Date/Time Offset (NM) (optional)
    $data[] = null;
    
    // AIL-8: Start Date/Time Offset Units (CE) (optional)
    $data[] = null;
    
    // AIL-9: Duration (NM) (optional)
    $data[] = null;
    
    // AIL-10: Duration Units (CE) (optional)
    $data[] = null;
    
    // AIL-11: Allow Substitution Code (IS) (optional)
    $data[] = null;
    
    // AIL-12: Filler Status Code (CE) (optional)
    $data[] = $this->getFillerStatutsCode($appointment);
    
    $this->fill($data);
  }    
} 
