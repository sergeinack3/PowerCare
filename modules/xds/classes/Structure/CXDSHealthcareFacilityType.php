<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe classification représentant la variable HealthcareFacilityType
 * Ensemble de métadonnées représentant la modalité d?exercice, liée à la rencontre du patient avec
 * l?organisation de soins, en rapport avec le document produit.
 */
class CXDSHealthcareFacilityType extends CXDSClass {

  /**
   * @see parent::__construct()
   */
  function __construct($id, $classifiedObject, $nodeRepresentation) {
    parent::__construct($id, $classifiedObject, $nodeRepresentation);
    $this->classificationScheme = "urn:uuid:f33fb8ac-18af-42cc-ae0e-ed0b0bdb91e1";
  }
}