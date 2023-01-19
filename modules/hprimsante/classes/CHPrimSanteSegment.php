<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2SegmentGroup;

/**
 * Hprim sante segment
 */
class CHPrimSanteSegment extends CHL7v2Segment {
  /**
   * @inheritdoc
   */
  static function create($name, CHL7v2SegmentGroup $parent) {
    $class = "CHPrimSanteSegment$name";
    $segment = class_exists($class) ? new $class($parent) : new self($parent);
    $segment->name = $name;

    return $segment;
  }
}