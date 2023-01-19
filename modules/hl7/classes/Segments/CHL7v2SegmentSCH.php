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
use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHL7v2SegmentSCH
 * SCH - Represents an HL7 SCH message segment (Scheduling Activity Information)
 */

class CHL7v2SegmentSCH extends CHL7v2Segment {

  /** @var string */
  public $name = "SCH";
  

  /** @var CConsultation */
  public $appointment;

  /**
   * Build SCH segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   * @throws CHL7v2Exception
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    
    $receiver = $event->_receiver;
    $message  = $event->message;
    $appointment = $this->appointment;

    if (CModule::getActive("appFine") && $appointment instanceof CEvenementMedical) {
      return $this->fill(CAppFineServer::generateSegmentSCH($appointment, $event->_receiver));
    }

    $appointment->loadRefPlageConsult();

    $data = array();
    
    // SCH-1: Placer Appointment ID (EI) (optional)
    $data[] = null;
    
    // SCH-2: Filler Appointment ID (EI) (optional)
    $data[] = $this->getFillerAppointmentID($appointment, $receiver);
    
    // SCH-3: Occurrence Number (NM) (optional)
    $data[] = null;
    
    // SCH-4: Placer Group Number (EI) (optional)
    $data[] = null;
    
    // SCH-5: Schedule ID (CE) (optional)
    $data[] = null;
    
    // SCH-6: Event Reason (CE)
    $motif = str_replace("\n", $message->componentSeparator, CMbString::truncate($appointment->motif, 187, "..."));
    $data[] = array(
      array(
        null,
        $motif
      )
    );
    
    // SCH-7: Appointment Reason (CE) (optional)
    $data[] = array(
      array(
        null,
        $motif
      )
    );
    
    // SCH-8: Appointment Type (CE) (optional)
    $category = $appointment->loadRefCategorie();
    $data[] = array(
      array(
        $category->_id,
        $category->nom_categorie
      )
    );

    // SCH-9: Appointment Duration (NM) (optional)
    $data[] = $appointment->_duree;
    
    // SCH-10: Appointment Duration Units (CE) (optional)
    $data[] = "M";
    
    // SCH-11: Appointment Timing Quantity (TQ) (optional repeating)
    $data[] = array (
      array(
        null,
        null,
        // Durée (M puis le nb de minutes)
        $appointment->_duree,
        $appointment->_datetime,
        $appointment->_date_fin,
      )
    );
    
    // SCH-12: Placer Contact Person (XCN) (optional repeating)
    $data[] = $this->getXCN($appointment->loadRefPraticien(false), $receiver);
    
    // SCH-13: Placer Contact Phone Number (XTN) (optional)
    $data[] = null;
    
    // SCH-14: Placer Contact Address (XAD) (optional repeating)
    $data[] = null;
    
    // SCH-15: Placer Contact Location (PL) (optional)
    $data[] = null;
    
    // SCH-16: Filler Contact Person (XCN) ( repeating)
    $first_log = $appointment->loadFirstLog();
    $mediuser = $first_log->loadRefUser()->loadRefMediuser();
    $data[] = $this->getXCN($mediuser, $receiver);
    
    // SCH-17: Filler Contact Phone Number (XTN) (optional)
    $data[] = null;
    
    // SCH-18: Filler Contact Address (XAD) (optional repeating)
    $data[] = null;
    
    // SCH-19: Filler Contact Location (PL) (optional)
    $data[] = null;
    
    // SCH-20: Entered By Person (XCN) ( repeating)
    $data[] = $this->getXCN($mediuser, $receiver);
    
    // SCH-21: Entered By Phone Number (XTN) (optional repeating)
    $data[] = null;
    
    // SCH-22: Entered By Location (PL) (optional)
    $data[] = null;
    
    // SCH-23: Parent Placer Appointment ID (EI) (optional)
    $data[] = null;
    
    // SCH-24: Parent Filler Appointment ID (EI) (optional)
    $data[] = null;
    
    // SCH-25: Filler Status Code (CE) (optional)
    $data[] = $this->getFillerStatutsCode($appointment);
    
    // SCH-26: Placer Order Number (EI) (optional repeating)
    $data[] = null;
    
    // SCH-27: Filler Order Number (EI) (optional repeating)
    $data[] = null;
       
    $this->fill($data);
  } 
} 
