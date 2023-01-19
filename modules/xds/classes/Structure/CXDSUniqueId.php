<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe ExternalIdentifier représentant la variable UniqueId
 * Cette métadonnée représente l?identifiant unique global affecté au document par son créateur.
 */
class CXDSUniqueId extends CXDSExternalIdentifier {

  /**
   * Construction de l'instance
   *
   * @param String $id              String
   * @param String $registryObject  String
   * @param String $value           String
   * @param bool   $registryPackage false
   */
  function __construct($id, $registryObject, $value, $registryPackage = false) {
    parent::__construct($id, $registryObject, $value);
    $this->identificationScheme = "urn:uuid:2e82c1f6-a085-4c72-9da3-8640a32e42ab";
    $name = "XDSDocumentEntry";
    if ($registryPackage) {
      $this->identificationScheme = "urn:uuid:96fdda7c-d067-4183-912e-bf5ee74998a8";
      $name = "XDSSubmissionSet";
    }
    $this->name = new CXDSName("$name.uniqueId");
  }
}