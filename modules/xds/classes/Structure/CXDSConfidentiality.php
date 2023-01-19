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
 * Classe classification repr�sentant la variable confidentiality d"ExtrinsicObject
 * Ensemble de m�tadonn�es contenant les informations d�finissant le niveau de confidentialit� d?un
 * document d�pos� dans l?entrep�t.
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
   * Retourne un type confidentialit� pour le masque pass� en param�tre
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
