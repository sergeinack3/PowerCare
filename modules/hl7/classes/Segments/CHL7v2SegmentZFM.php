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
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentZFM
 * ZFM - Represents an HL7 ZFM message segment (Mouvement PMSI)
 */

class CHL7v2SegmentZFM extends CHL7v2Segment {

  /** @var string */
  public $name   = "ZFM";
  

  /** @var CSejour */
  public $sejour;

  /** @var CAffectation */
  public $curr_affectation;

  /**
   * Build ZFM segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   * @throws CHL7v2Exception
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $sejour      = $this->sejour;
    $affectation = $this->curr_affectation;

    // ZFM-1: Mode d'entrée PMSI
    $data[] = $sejour->mode_entree;
    
    // ZFM-2: Mode de sortie PMSI
    // normal - transfert - mutation - deces
    $data[] = ($affectation && $affectation->_id && $affectation->mode_sortie != null) ? $affectation->mode_sortie : $this->getModeSortie($sejour);
    
    // ZFM-3: Mode de provenance PMSI
    $data[] = $this->getModeProvenance($sejour);
    
    // ZFM-4: Mode de destination PMSI
    $destination = ($affectation && $affectation->_id && $affectation->destination != null) ? $affectation->destination : $sejour->destination;
    $data[] = $destination == "0" ? null : $destination;
    
    $this->fill($data);
  }
}