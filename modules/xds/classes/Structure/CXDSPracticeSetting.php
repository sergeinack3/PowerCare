<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe classification repr�sentant la variable Practicesetting
 * Ensemble de m�tadonn�es repr�sentant le cadre d?exercice de l?acte qui a engendr� la cr�ation du
 * document.
 */
class CXDSPracticeSetting extends CXDSClass {

  /**
   * @see parent::__construct()
   */
  function __construct($id, $classifiedObject, $nodeRepresentation) {
    parent::__construct($id, $classifiedObject, $nodeRepresentation);
    $this->classificationScheme = "urn:uuid:cccf5598-8b07-4b77-a05e-ae952c785ead";
  }
}