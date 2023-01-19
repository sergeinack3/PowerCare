<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Datatypes\Base\CCDA_base_st;
use Ox\Interop\Cda\Datatypes\Base\CCDA_base_uid;

/**
 * POCD_MT000040_InfrastructureRoot_typeId Class
 */
class CCDAPOCD_MT000040_InfrastructureRoot_typeId extends CCDAClasseCda {

  /**
   * @var CCDA_base_uid
   */
  public $root;

  /**
   * @var CCDA_base_st
   */
  public $extension;

  /**
   * Setter Extension
   *
   * @param String $extension String
   *
   * @return void
   */
  public function setExtension($extension) {
    if (!$extension) {
      $this->extension = null;
      return;
    }
    $st = new CCDA_base_st();
    $st->setData($extension);
    $this->extension = $st;
  }

  /**
   * Getter extension
   *
   * @return CCDA_base_st
   */
  public function getExtension() {
    return $this->extension;
  }

  /**
   * Setter root
   *
   * @param String $root String
   *
   * @return void
   */
  public function setRoot($root) {
    if (!$root) {
      $this->root = null;
      return;
    }
    $uid = new CCDA_base_uid();
    $uid->setData($root);
    $this->root = $uid;
  }

  /**
   * Getter root
   *
   * @return CCDA_base_uid
   */
  public function getRoot() {
    return $this->root;
  }

  /**
   * Assigne les valeurs pas défaut à l'instanciation de la classe
   */
  function __construct() {
    $this->setRoot("2.16.840.1.113883.1.3");
    $this->setExtension("POCD_HD000040");
  }

  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["root"]      = "CCDA_base_uid xml|attribute required fixed|2.16.840.1.113883.1.3";
    $props["extension"] = "CCDA_base_st xml|attribute required";
    return $props;
  }

  /**
   * Fonction permettant de tester la classe
   *
   * @return array
   */
  function test() {
    $tabTest = array();

    /**
     * Test avec les valeurs null
     */

    $tabTest[] = $this->sample("Test avec les valeurs par défaut", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}