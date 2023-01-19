<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hprimsante\CHPrimSanteSegment;

/**
 * Class CHPrimSanteSegmentL
 * L - Represents an HPR L message segment (Message Footer)
 */
class CHPrimSanteSegmentL extends CHPrimSanteSegment {
  public $name = "L";

  /**
   * @inheritdoc
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $data = array();

    // L-1 : Segment Row (optional)
    $data[] = null;

    // L-2 : Not Use (optional)
    $data[] = null;

    // L-3 : Number Segment P (optional)
    $data[] = null;

    // L-4 : Number Segment of Message (optional)
    $data[] = null;

    // L-5 : Lot Number (optional)
    $data[] = null;

    $this->fill($data);
  }
}
