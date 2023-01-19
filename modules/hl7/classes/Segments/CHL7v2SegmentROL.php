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
    // AD - ADD - Nouveau rôle du médecin
    // DE - DELETE - Suppression du rôle du médecin
    // UC - UNCHANGED - Notification du médecin à prendre en compte pour le rôle défini dans le contexte courant
    // UP - UPDATE - Mise à jour du rôle du médecin
    $data[] = $this->action;
     
    // ROL-3: Role-ROL (CE)
    // Table - 0443
    // AD   - Admitting - PV1.17 Médecin de la structure qui décide d'hospitaliser
    // AT   - Attending - PV1-7 Médecin responsable du patient pendant le séjour
    // CP   - Consulting Provider - Médecin consulté pour 2ème avis dans le cadre de la venue
    // FHCP - Family Health Care Professional - Médecin de famille. Utilisé dans les rares cas où il est distinct du médecin traitant
    // RP   - Referring Provider - PV1-8 Médecin adressant 
    // RT   - Referred to Provider - Médecin correspondant
    // ODRP - Officialy Declared Referring Physician - Médecin Traitant
    // SUBS - Substitute - Remplaçant du médecin traitant
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