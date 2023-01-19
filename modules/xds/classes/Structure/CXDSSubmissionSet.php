<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe classification réprésentant une submissionSet
 */
class CXDSSubmissionSet extends CXDSClassification {

  public $classificationNode;

  /**
   * Construction de l'instance
   *
   * @param String $id               String
   * @param String $classifiedObject String
   */
  function __construct($id, $classifiedObject) {
    parent::__construct($id);
    $this->classificationNode = "urn:uuid:a54d6aa5-d40d-43f9-88c5-b4633d873bdd";
    $this->classifiedObject = $classifiedObject;
  }

  /**
   * @inheritdoc
   */
  function toXML($submissionSet = false) {
    return parent::toXML(true);
  }
}