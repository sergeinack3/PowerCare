<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe ExternalIdentifier représentant la variable PatientId
 */
class CXDSPatientID extends CXDSExternalIdentifier {

  /**
   * Construction de l'instance
   *
   * @param String $id             String
   * @param String $registryObject String
   * @param String $value          String
   * @param bool   $registry       false
   */
  function __construct($id, $registryObject, $value, $registry = false) {
    parent::__construct($id, $registryObject, $value);
    $this->identificationScheme = "urn:uuid:58a6f841-87b3-4a3e-92fd-a8ffeff98427";
    $name = "XDSDocumentEntry";
    if ($registry) {
      $this->identificationScheme = "urn:uuid:6b5aea1a-874d-4603-a4bc-96a0a7b38446";
      $name = "XDSSubmissionSet";
    }
    $this->name = new CXDSName("$name.patientId");
  }
}