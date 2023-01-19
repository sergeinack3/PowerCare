<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationType;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Consumable Class
 */
class CCDAPOCD_MT000040_Consumable extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_ManufacturedProduct
   */
  public $manufacturedProduct;

  /**
   * Setter manufacturedProduct
   *
   * @param CCDAPOCD_MT000040_ManufacturedProduct $inst CCDAPOCD_MT000040_ManufacturedProduct
   *
   * @return void
   */
  function setManufacturedProduct(CCDAPOCD_MT000040_ManufacturedProduct $inst) {
    $this->manufacturedProduct = $inst;
  }

  /**
   * Getter manufacturedProduct
   *
   * @return CCDAPOCD_MT000040_ManufacturedProduct
   */
  function getManufacturedProduct() {
    return $this->manufacturedProduct;
  }

  /**
   * Assigne typeCode à CSM
   *
   * @return void
   */
  function setTypeCode() {
    $particip = new CCDAParticipationType();
    $particip->setData("CSM");
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
    $props["typeId"]              = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["manufacturedProduct"] = "CCDAPOCD_MT000040_ManufacturedProduct xml|element required";
    $props["typeCode"]            = "CCDAParticipationType xml|attribute fixed|CSM";
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
     * Test avec un manufacturedProduct correct
     */

    $manuProd = new CCDAPOCD_MT000040_ManufacturedProduct();
    $label = new CCDAPOCD_MT000040_LabeledDrug();
    $label->setClassCode();
    $manuProd->setManufacturedLabeledDrug($label);
    $this->setManufacturedProduct($manuProd);
    $tabTest[] = $this->sample("Test avec un manufacturedProduct correct", "Document valide");

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