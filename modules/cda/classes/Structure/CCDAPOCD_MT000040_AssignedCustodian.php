<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClassAssignedEntity;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_AssignedCustodian Class
 */
class CCDAPOCD_MT000040_AssignedCustodian extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_CustodianOrganization
   */
  public $representedCustodianOrganization;

  /**
   * Setter classCode
   *
   * @return void
   */
  function setClassCode() {
    $roleClass = new CCDARoleClassAssignedEntity();
    $roleClass->setData("ASSIGNED");
    $this->classCode = $roleClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDARoleClassAssignedEntity
   */
  function getClassCode() {
    return $this->classCode;
  }



  /**
   * Setter representedCustodianOrganization
   *
   * @param CCDAPOCD_MT000040_CustodianOrganization $inst CCDAPOCD_MT000040_CustodianOrganization
   *
   * @return void
   */
  function setRepresentedCustodianOrganization(CCDAPOCD_MT000040_CustodianOrganization $inst) {
    $this->representedCustodianOrganization = $inst;
  }

  /**
   * Getter representedCustodianOrganization
   *
   * @return CCDAPOCD_MT000040_CustodianOrganization
   */
  function getRepresentedCustodianOrganization() {
    return $this->representedCustodianOrganization;
  }

  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]                           = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["representedCustodianOrganization"] = "CCDAPOCD_MT000040_CustodianOrganization xml|element required";
    $props["classCode"]                        = "CCDARoleClassAssignedEntity xml|attribute fixed|ASSIGNED";
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
     * Test avec un representedCustodianOrganization correct
     */

    $custoOrg = new CCDAPOCD_MT000040_CustodianOrganization();
    $ii = new CCDAII();
    $ii->setRoot("1.25.2");
    $custoOrg->appendId($ii);
    $this->setRepresentedCustodianOrganization($custoOrg);
    $tabTest[] = $this->sample("Test avec un representedCustodianOrganization correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}