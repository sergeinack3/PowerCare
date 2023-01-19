<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\MFN;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CEntity;
use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentLCH;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentLOC;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentLRL;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentMFE;
use Ox\Mediboard\Etablissement\CLegalEntity;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Classe CHL7v2EventMFN
 * Transporter des structures spécifiques dans des messages HL7
 */
class CHL7v2EventMFN extends CHL7v2Event implements CHL7EventMFN {
  /** @var string */
  public $event_type = "MFN";

  public static $entities = array(
      'M'           => 'CLegalEntity',
      'ETBL_GRPQ'   => 'CGroups',
      'D'           => 'CService',
      'H'           => 'CUniteFonctionnelle',
      'N'           => 'CUniteFonctionnelle',
      'B'           => 'CLit',
      'STRCTR_INTR' => 'CInternalStructure',
      'R'           => 'CChambre',
  );

  /**
   * Recupère l'id du parent de l'entité
   *
   * @param CEntity $entity entite
   *
   * @return integer
   * @throws Exception
   */
  function getParentEntityId($entity) {
    $primary_key_entity = array_search(CClassMap::getSN($entity), CHL7v2EventMFN::$entities);

    $value_id = null;

    switch ($primary_key_entity) {
      // Etablissement
      case 'ETBL_GRPQ':
        if ($entity->legal_entity_id) {
          $value_id = $entity->legal_entity_id;
        }
        break;

      // Service
      case 'D':
        if ($entity->group_id) {
          $value_id = $entity->group_id;
        }
        break;

      // Lit
      case 'B':
        if ($entity->chambre_id) {
          $value_id = $entity->chambre_id;
        }
        break;

      // Chambre
      case 'R':
        if ($entity->service_id) {
          $value_id = $entity->service_id;
        }
        break;

      default:
    }

    return $value_id;
  }

  /**
   * Construct
   *
   * @param string $i18n i18n
   */
  function __construct($i18n = null) {
    $this->profil    = "MFN";
    $this->msg_codes = array(
        array(
            $this->event_type, $this->code, "{$this->event_type}_{$this->struct_code}"
        )
    );
  }

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    parent::build($object);

    // Message Header
    $this->addMSH();

    // Master File Identification
    $this->addMFI();

    foreach ($object->_objects as $_entity) {
      // Master File Entry
      $this->addMFE($_entity);

      // Location Identification
      $this->addLOC($_entity);

      // Location Characteristic
      foreach (CHL7v2TableEntry::getTable("9002", false) as $_code) {
        if (!$_code) {
          continue;
        }

        $value = CHL7v2TableEntry::mapTo("9002", $_code);

        $user = null;
        if (preg_match("#RSPNSBL#", $value)) {
          $user = $_entity->loadRefUser();
          if ($user && $user->$_code == null) {
            continue;
          }
        }
        else {
          if ($_entity->$_code == null) {
            continue;
          }
        }
        $this->addLCH($_entity, $_code, $value, $user);
      }

      // Si l'objet est une Entité juridique => pas de segment LRL
      if (get_class($_entity) == CLegalEntity::class) {
        continue;
      }

      // Si l'id du parent de l'entité est vide => pas de segment LRL
      if (!$this->getParentEntityId($_entity)) {
        continue;
      }

      // Location Relationship
      $this->addLRL($_entity);
    }
  }

  /**
   * MSH - Represents an HL7 MSH message segment (Message Header)
   *
   * @return void
   */
  function addMSH() {
    $MSH = CHL7v2Segment::create("MSH", $this->message);
    $MSH->build($this);
  }

  /**
   * MFI - Represents an HL7 MFI message segment (Master File Identification)
   *
   * @return void
   */
  function addMFI() {
    $MFI = CHL7v2Segment::create("MFI", $this->message);
    $MFI->build($this);
  }

  /**
   * MFE - Represents an HL7 MFE message segment (Master File Entry)
   *
   * @param CEntity $entity entity
   *
   * @return void
   */
  function addMFE($entity) {
    /** @var CHL7v2SegmentMFE $MFE */
    $MFE         = CHL7v2Segment::create("MFE", $this->message);
    $MFE->entity = $entity;
    $MFE->build($this, $entity);
  }

  /**
   * LOC - Represents an HL7 LOC message segment (Location Identification)
   *
   * @param CEntity $entity entity
   *
   * @return void
   */
  function addLOC($entity) {
    /** @var CHL7v2SegmentLOC $LOC */
    $LOC         = CHL7v2Segment::create("LOC", $this->message);
    $LOC->entity = $entity;
    $LOC->build($this);
  }

  /**
   * LCH - Represents an HL7 LCH message segment (Location Characteristic)
   *
   * @param CEntity          $entity entity
   * @param CEntity          $code   code
   * @param CHL7v2TableEntry $value  value
   * @param CMediusers       $user   user
   *
   * @return void
   */
  function addLCH($entity, $code, $value, $user) {
    /** @var CHL7v2SegmentLCH $LCH */
    $LCH         = CHL7v2Segment::create("LCH", $this->message);
    $LCH->entity = $entity;
    $LCH->code   = $code;
    $LCH->value  = $value;
    $LCH->user   = $user;
    $LCH->build($this);
  }

  /**
   * LRL - Represents an HL7 LRL message segment (Location Relationship)
   *
   * @return void
   */
  function addLRL($entity) {
    /** @var CHL7v2SegmentLRL $LRL */
    $LRL         = CHL7v2Segment::create("LRL", $this->message);
    $LRL->entity = $entity;
    $LRL->build($this);
  }
}