<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CMbArray;
use Ox\Interop\Hl7\CHEvent;
use Ox\Mediboard\Hospi\CMovement;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentZBE_FR
 * ZBE - Represents an HL7 ZBE message segment (Movement)
 */


class CHL7v2SegmentZBE_FR extends CHL7v2SegmentZBE {
  /**
   * @inheritdoc
   */
  function fillOtherSegments(&$data, $ufs, CHEvent $event, CSejour $sejour, CMovement $movement = null) {
    // ZBE-8: Ward of care responsibility in the period starting with this movement (XON) (optional)
    $uf_type = $event->_receiver->_configs["build_ZBE_8"];
    /** @var CUniteFonctionnelle $uf_soins */
    $uf_soins = isset($ufs[$uf_type]) ? $ufs[$uf_type] : null;
    if (isset($uf_soins->_id)) {
      $data[] = array(
        array(
          // ZBE-8.1 : Libellé de l'UF
          $uf_soins->libelle,
          null,
          null,
          null,
          null,
          // ZBE-8.6 : Identifiant de l'autorité d'affectation  qui a attribué l'identifiant de l'UF de responsabilité médicale
          $this->getAssigningAuthority("mediboard", null, null, null, $this->sejour->group_id),
          // ZBE-8.7 : La seule valeur utilisable de la table 203 est "UF"
          "UF",
          null,
          null,
          // ZBE-8.10 : Identifiant de l'UF de responsabilité médicale
          $uf_soins->code
        )
      );
    }
    else {
      $data[] = null;
    }
    
    // ZBE-9: Nature of this movement (CWE)
    // S   - Changement de responsabilité de soins uniquement
    // H   - Changement de responsabilité d'hébergement uniquement
    // M   - Changement de responsabilité médicale uniquement
    // L   - Changement de lit uniquement
    // D   - Changement de prise en charge médico-administrative laissant les responsabilités et la localisation du patient inchangées
    //       (ex : changement de tarif du séjour en unité de soins)
    // SM  - Changement de responsabilité soins + médicale
    // SH  - Changement de responsabilité soins + hébergement
    // MH  - Changement de responsabilité hébergement + médicale
    // LD  - Changement de prise en charge médico-administrative et de lit, laissant les responsabilités inchangées
    // HMS - Changement conjoint des trois responsabilités.
    // C   - Correction ou changement du statut administratif du patient sans génération de mouvement

    // Changement d'UF médicale
    if ($sejour->fieldModified("type") &&
      ($movement->original_trigger_code == "A05" || $movement->original_trigger_code == "A01" ||
        $movement->original_trigger_code == "A04")) {
      $data[] = "C";
    }
    elseif ($sejour->fieldModified("uf_hebergement_id")) {
      $data[] = "H";
    }
    elseif (CMbArray::in($event->code, "Z80 Z81 Z82 Z83") || $sejour->fieldModified("uf_medicale_id")) {
      $data[] = "M";
    }
    // Changement d'UF de soins
    elseif (CMbArray::in($event->code, "Z84 Z85 Z86 Z87") || $sejour->fieldModified("uf_soins_id")) {
      $data[] = "S";
    }
    else {
      $data[] = "HMS";
    }
  }
}
