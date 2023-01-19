<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2SegmentZFS
 * ZFS - Represents an HL7 ZFV message segment (Mode le?gal de soins en psychiatrie)
 */

class CHL7v2SegmentZFS extends CHL7v2Segment {

  /** @var string */
  public $name   = "ZFS";
  

  /** @var CPatient */
  public $patient;

  /**
   * Build ZFS segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   * @throws CHL7v2Exception
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $patient = $this->patient;

    // ZFS-1: Set ID - ZFS
    $data[] = null;

    // ZFS-2: Identifiant du mode légal de soin
    $data[] = null;

    // ZFS-3: Date et heure du début du mode légal de soin
    $data[] = null;

    // ZFS-4: Date et heure de la fin du mode légal de soin
    $data[] = null;

    // ZFS-5: Action du mode légal de soin
    $data[] = null;

    // ZFS-6: Mode légal de soins
    $data[] = null;

    // ZFS-7: Code RIM-P du mode légal de soin
    $data[] = null;

    // ZFS-8: Commentaire
    $data[] = null;

    $this->fill($data);
  }
}