<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActRelationshipFulfills;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;
/**
 * POCD_MT000040_InFulfillmentOf Class
 */
class CCDAPOCD_MT000040_InFulfillmentOf extends CCDARIMActRelationship {

  /**
   * @var CCDAPOCD_MT000040_Order
   */
  public $order;

  /**
   * Setter order
   *
   * @param CCDAPOCD_MT000040_Order $inst CCDAPOCD_MT000040_Order
   *
   * @return void
   */
  function setOrder(CCDAPOCD_MT000040_Order $inst) {
    $this->order = $inst;
  }

  /**
   * Getter order
   *
   * @return CCDAPOCD_MT000040_Order
   */
  function getOrder() {
    return $this->order;
  }

  /**
   * Assigne typeCode à FLFS
   *
   * @return void
   */
  function setTypeCode() {
    $actRela = new CCDAActRelationshipFulfills();
    $actRela->setData("FLFS");
    $this->typeCode = $actRela;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAActRelationshipFulfills
   */
  function getTypeCode() {
    return $this->typeCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]   = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["order"]    = "CCDAPOCD_MT000040_Order xml|element required";
    $props["typeCode"] = "CCDAActRelationshipFulfills xml|attribute fixed|FLFS";
    return $props;
  }

  /**
   * Fonction permettant de tester la classe
   *
   * @return array
   */
  function test() {
    $tabTest = parent::test();

    /**
     * Test avec un order correct
     */

    $ord = new CCDAPOCD_MT000040_Order();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $ord->appendId($ii);
    $this->setOrder($ord);
    $tabTest[] = $this->sample("Test avec un order correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correct
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}