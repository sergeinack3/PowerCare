<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

use Ox\Core\CMbArray;
use Ox\Interop\InteropResources\CInteropResources;
use Ox\Interop\InteropResources\valueset\CANSValueSet;

/**
 * Classe classification représentant la variable confidentiality d"ExtrinsicObject
 * Ensemble de métadonnées contenant les informations définissant le niveau de confidentialité d?un
 * document déposé dans l?entrepôt.
 */
class CXDSConfidentiality extends CXDSClass {

  /**
   * Construction de la classe
   *
   * @param String $id                 String
   * @param String $classifiedObject   String
   * @param String $nodeRepresentation String
   */
  function __construct($id, $classifiedObject, $nodeRepresentation) {
    parent::__construct($id, $classifiedObject, $nodeRepresentation);
    $this->classificationScheme = "urn:uuid:f4f85eac-e6cb-4883-b524-f2705394840f";
  }

  /**
   * Retourne un type confidentialité pour le masque passé en paramètre
   *
   * @param String $id               Identifiant
   * @param String $classifiedObject ClassifiedObject
   * @param String $code             Code
   *
   * @throws
   * @return CXDSConfidentiality
   */
  static function getMasquage($id, $classifiedObject, $code) {
    $values = CANSValueSet::loadEntries("confidentialityCode", $code);

    $confidentiality = new CXDSConfidentiality($id, $classifiedObject, CMbArray::get($values, "code"));
    $confidentiality->setCodingScheme(array(CMbArray::get($values, "codeSystem")));
    $confidentiality->setName(CMbArray::get($values, "displayName"));
    return $confidentiality;
  }
}
