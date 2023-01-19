<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe ExternalIdentifier représentant la variable SourceId
 */
class CXDSSourceId extends CXDSExternalIdentifier {

  /**
   * @see parent::__construct()
   */
  function __construct($id, $registryObject, $value) {
    parent::__construct($id, $registryObject, $value);
    $this->identificationScheme = "urn:uuid:554ac39e-e3fe-47fe-b233-965d2a147832";
    $this->name = new CXDSName("XDSSubmissionSet.sourceId");
  }
}