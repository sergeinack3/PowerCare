<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationType;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Custodian Class
 */
class CCDAPOCD_MT000040_Custodian extends CCDARIMParticipation {


  /**
   * @var CCDAPOCD_MT000040_AssignedCustodian
   */
  public $assignedCustodian;

  /**
   * Setter assignedCustodian
   *
   * @param CCDAPOCD_MT000040_AssignedCustodian $inst CCDAPOCD_MT000040_AssignedCustodian
   *
   * @return void
   */
  function setAssignedCustodian(CCDAPOCD_MT000040_AssignedCustodian $inst) {
    $this->assignedCustodian = $inst;
  }

  /**
   * Getter assignedCustodian
   *
   * @return CCDAPOCD_MT000040_AssignedCustodian
   */
  function getAssignedCustodian() {
    return $this->assignedCustodian;
  }

  /**
   * Assigne typeCode à CST
   *
   * @return void
   */
  function setTypeCode() {
    $particip = new CCDAParticipationType();
    $particip->setData("CST");
    $this->typeCode = $particip;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAParticipationType
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
    $props["typeId"]            = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["assignedCustodian"] = "CCDAPOCD_MT000040_AssignedCustodian xml|element required";
    $props["typeCode"]          = "CCDAParticipationType xml|attribute fixed|CST";
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
     * Test avec un assignedCustodian correct
     */

    $assign = new CCDAPOCD_MT000040_AssignedCustodian();
    $custoOrg = new CCDAPOCD_MT000040_CustodianOrganization();
    $ii = new CCDAII();
    $ii->setRoot("1.25.2");
    $custoOrg->appendId($ii);
    $assign->setRepresentedCustodianOrganization($custoOrg);
    $this->setAssignedCustodian($assign);
    $tabTest[] = $this->sample("Test avec un assignedCustodian correct", "Document valide");

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