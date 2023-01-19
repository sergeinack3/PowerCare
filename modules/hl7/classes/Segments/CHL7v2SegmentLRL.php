<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CClassMap;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\MFN\CHL7v2EventMFN;

/**
 * Class CHL7v2SegmentLRL
 * LRL - Represents an HL7 LRL message segment (Transporte les liens entre entités)
 */
class CHL7v2SegmentLRL extends CHL7v2Segment {

  /** @var string */
  public $name = "LRL";

  public $entity;

  /**
   * Build LRL segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $entity = $this->entity;

    $primary_key_entity = array_search(CClassMap::getSN($entity), CHL7v2EventMFN::$entities);
    $primary_key = $primary_key_entity . $entity->_id;

    // LRL-1: Primary Key Value-LRL - LRL (PL) (Requis)
    $data[] = $primary_key;

    // LRL-2: Segment Action Code (ID) (Optional)
    $data[] = null;

    // LRL-3: Segment Unique Key - LRL (EI) (Optional)
    $data[] = null;

    // LRL-4: Location Relationship ID - LRL (CWE) (Requis)
    $typology = null;
    $primary_key_entity_associated = null;

    switch ($primary_key_entity) {
      // Etablissement
      case 'ETBL_GRPQ':
        $typology = "ETBLSMNT";
        if ($entity->legal_entity_id) {
          $primary_key_entity_associated = "M$entity->legal_entity_id";
        }
        break;

      // Service
      case 'D':
        $typology = "LCLSTN";
        $primary_key_entity_associated = "ETBL_GRPQ$entity->group_id";
        break;

      case 'N':
        $typology = "RSPNSBLT";
        break;

      case 'H':
        $typology = "RSPNSBLT";
        break;

      // Lit
      case 'B':
        $typology = "LCLSTN";
        $primary_key_entity_associated = "R$entity->chambre_id";
        break;

      // Chambre
      case 'R':
        $typology = "LCLSTN";
        $primary_key_entity_associated = "D$entity->service_id";
        break;

      default:
    }

    $data[] = array(
        $typology
    );

    // LRL-5: Organizational Location Relationship Value - LRL (XON) (Conditional)
    $data[] = null;

    // LRL-6: Patient Location Relationship Value - LRL (PL) (Conditional)
    $data[] = $primary_key_entity_associated;

    $this->fill($data);
  }
}