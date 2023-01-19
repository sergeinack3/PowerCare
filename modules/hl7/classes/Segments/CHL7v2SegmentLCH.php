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
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Interop\Hl7\Events\MFN\CHL7v2EventMFN;

/**
 * Class CHL7v2SegmentLCH
 * LCH - Represents an HL7 LCH message segment (Transporte des attributs supplémentaires non définis dans le segment LOC)
 */
class CHL7v2SegmentLCH extends CHL7v2Segment {

  /** @var string */
  public $name = "LCH";

  public $entity;
  public $code;
  public $value;
  public $user;

  public static $LCHKey = array('code', 'description', 'user_id', 'user_last_name', 'user_first_name', 'user_phone',
      'opening_date', 'closing_date', 'activation_date', 'inactivation_date'
  );

  /**
   * Build LCH segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $entity = $this->entity;
    $code   = $this->code;
    $user   = $this->user;

    $primary_key_value = array_search(CClassMap::getSN($entity), CHL7v2EventMFN::$entities);
    $primary_key       = $primary_key_value . $entity->_id;

    // LCH-1: Primary Key Value -LCH - LCH (PL) (Requis)
    $data[] = $primary_key;

    // LCH-2: Segment Action Code - LCH (ID) (Optional)
    $data[] = null;

    // LCH-3: Segment Unique Key - LCH (EI) (Optional)
    $data[] = null;

    // LCH-4: Location Characteristic ID - LCH (CWE) (Requis)
    $data[] = array(
        array(
            $this->value,
            CHL7v2TableEntry::getDescription("9002", $this->value)
        )
    );

    // LCH-5: Location Characteristic Value - LCH (CWE) (Requis)
    $data[] = $user ? $user->$code : $entity->$code;

    $this->fill($data);
  }
}