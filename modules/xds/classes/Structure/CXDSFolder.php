<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe classification réprésentant un folder
 */
class CXDSFolder extends CXDSClassification {

  public $classificationNode;

  /**
   * Construction de l'instance
   *
   * @param String $id               String
   * @param String $classifiedObject String
   */
  function __construct($id, $classifiedObject) {
    parent::__construct($id);
    $this->classificationNode = "urn:uuid:d9d542f3-6cc4-48b6-8870-ea235fbc94c2";
    $this->classifiedObject   = $classifiedObject;
  }

  /**
   * @inheritdoc
   */
  function toXML($submissionSet = true) {
    return parent::toXML(true);
  }
}