<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Class CHL7v2SegmentROL 
 * ROL - Represents an HL7 ROL message segment (Role)
 */

class CHL7v2SegmentROL extends CHL7v2Segment {

  /** @var string */
  public $name = "ROL";

  /** @var string */
  public $action  = "UC";

  /** @var null */
  public $role_id;
  

  /** @var CMedecin */
  public $medecin;

  /**
   * Build ROL segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
        
    $medecin  = $this->medecin;

    $data = array();
        
    // ROL-1: Role Instance ID (EI) (optional)
    // The field is optional when used in ADT and Finance messages
    $data[] = null;
    
    // ROL-2: Action Code (ID)
    // Table - 0287
    // AD - ADD - Nouveau r�le du m�decin
    // DE - DELETE - Suppression du r�le du m�decin
    // UC - UNCHANGED - Notification du m�decin � prendre en compte pour le r�le d�fini dans le contexte courant
    // UP - UPDATE - Mise � jour du r�le du m�decin
    $data[] = $this->action;
     
    // ROL-3: Role-ROL (CE)
    // Table - 0443
    // AD   - Admitting - PV1.17 M�decin de la structure qui d�cide d'hospitaliser
    // AT   - Attending - PV1-7 M�decin responsable du patient pendant le s�jour
    // CP   - Consulting Provider - M�decin consult� pour 2�me avis dans le cadre de la venue
    // FHCP - Family Health Care Professional - M�decin de famille. Utilis� dans les rares cas o� il est distinct du m�decin traitant
    // RP   - Referring Provider - PV1-8 M�decin adressant 
    // RT   - Referred to Provider - M�decin correspondant
    // ODRP - Officialy Declared Referring Physician - M�decin Traitant
    // SUBS - Substitute - Rempla�ant du m�decin traitant
    $data[] = array( 
      array (
        $this->role_id,
        null,
        null,
        null,
        null,
        null
      )
    );
     
    // ROL-4: Role Person (XCN) (repeating)
    $data[] = $this->getXCN($medecin, $event->_receiver, true);
    
    // ROL-5: Role Begin Date/Time (TS) (optional)
    $data[] = null;
    
    // ROL-6: Role End Date/Time (TS) (optional)
    $data[] = null;
    
    // ROL-7: Role Duration (CE) (optional)
    $data[] = null;
    
    // ROL-8: Role Action Reason (CE) (optional)
    $data[] = null;
    
    // ROL-9: Provider Type (CE) (optional repeating)
    $data[] = null;
    
    // ROL-10: Organization Unit Type (CE) (optional)
    $data[] = null;
    
    // ROL-11: Office/Home Address/Birthplace (XAD) (optional repeating)
    $data[] = null;
    
    // ROL-12: Phone (XTN) (optional repeating)
    $data[] = null;
    
    $this->fill($data);
  }
}