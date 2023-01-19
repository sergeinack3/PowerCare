<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CAppUI;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentZFV
 * ZFV - Represents an HL7 ZFV message segment (Complément d'information sur la venue)
 */

class CHL7v2SegmentZFV extends CHL7v2Segment {

  /** @var string */
  public $name   = "ZFV";
  

  /** @var CSejour */
  public $sejour;

  /** @var CAffectation */
  public $curr_affectation;

  /**
   * Build ZFV segement
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

    $etab_provenance = $sejour->loadRefEtablissementProvenance();
    // ZFV-1: Etablissement de provenance (DLD)
    if ($sejour->etablissement_entree_id) {
      if ($sejour->date_entree_reelle_provenance) {
        $data[] = array(
          array(
            $etab_provenance->finess,
            $sejour->date_entree_reelle_provenance,
          )
        );
      }
      else {
        $data[] = array(
          array(
            $etab_provenance->finess,
          )
        );
      }
    }
    else {
      $data[] = null;
    }
    
    // ZFV-2: Mode de transport de sortie
    $data[] = null;
    
    // ZFV-3: Type de préadmission
    $data[] = null;
    
    // ZFV-4: Date de début de placement (psy)
    $data[] = null;
    
    // ZFV-5: Date de fin de placement (psy)
    $data[] = null;
    
    // ZFV-6: Adresse de la provenance ou de la destination (XAD)
    $adresses = array();
    if ($sejour->etablissement_entree_id) {
      $adresses[] = array(
        str_replace("\n", "", $etab_provenance->adresse),
        null,
        $etab_provenance->ville,
        null,
        $etab_provenance->cp,
        null,
        "ORI"
      );
    }

    $etab_destination = new CEtabExterne();
    if ($affectation && $affectation->_id && $affectation->mode_sortie != null) {
      $etab_destination = $affectation->loadRefEtablissementTransfert();
    }
    elseif ($sejour->etablissement_sortie_id) {
      $etab_destination = $sejour->loadRefEtablissementTransfert();
    }
    //$etablissement_sortie_id =
    if ($etab_destination->_id) {
      $adresses[] = array(
        str_replace("\n", "", $etab_destination->adresse),
        null,
        $etab_destination->ville,
        null,
        $etab_destination->cp,
        null,
        "DST"
      );
    }
    $data[] = $adresses;
    
    // ZFV-7: NDA de l'établissement de provenance
    $data[] = null;

    // ZFV-8: Numéros d'archives
    $data[] = null;

    // ZFV-9: Mode de sortie personnalisée
    $data[] = CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie") ? $sejour->loadRefModeSortie()->code : null;

    $this->fill($data);
  }
}