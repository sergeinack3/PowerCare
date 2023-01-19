<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClassManufacturedProduct;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_ManufacturedProduct Class
 */
class CCDAPOCD_MT000040_ManufacturedProduct extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_LabeledDrug
   */
  public $manufacturedLabeledDrug;

  /**
   * @var CCDAPOCD_MT000040_Material
   */
  public $manufacturedMaterial;

  /**
   * @var CCDAPOCD_MT000040_Organization
   */
  public $manufacturerOrganization;
  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAII $inst CCDAII
   *
   * @return void
   */
  function appendId(CCDAII $inst) {
    array_push($this->id, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListId() {
    $this->id = array();
  }

  /**
   * Getter id
   *
   * @return CCDAII[]
   */
  function getId() {
    return $this->id;
  }

  /**
   * Setter manufacturedLabeledDrug
   *
   * @param CCDAPOCD_MT000040_LabeledDrug $inst CCDAPOCD_MT000040_LabeledDrug
   *
   * @return void
   */
  function setManufacturedLabeledDrug(CCDAPOCD_MT000040_LabeledDrug $inst) {
    $this->manufacturedLabeledDrug = $inst;
  }

  /**
   * Getter manufacturedLabeledDrug
   *
   * @return CCDAPOCD_MT000040_LabeledDrug
   */
  function getManufacturedLabeledDrug() {
    return $this->manufacturedLabeledDrug;
  }

  /**
   * Setter manufacturedMaterial
   *
   * @param CCDAPOCD_MT000040_Material $inst CCDAPOCD_MT000040_Material
   *
   * @return void
   */
  function setManufacturedMaterial(CCDAPOCD_MT000040_Material $inst) {
    $this->manufacturedMaterial = $inst;
  }

  /**
   * Getter manufacturedMaterial
   *
   * @return CCDAPOCD_MT000040_Material
   */
  function getManufacturedMaterial() {
    return $this->manufacturedMaterial;
  }

  /**
   * Setter manufacturerOrganization
   *
   * @param CCDAPOCD_MT000040_Organization $inst CCDAPOCD_MT000040_Organization
   *
   * @return void
   */
  function setManufacturerOrganization(CCDAPOCD_MT000040_Organization $inst) {
    $this->manufacturerOrganization = $inst;
  }

  /**
   * Getter manufacturerOrganization
   *
   * @return CCDAPOCD_MT000040_Organization
   */
  function getManufacturerOrganization() {
    return $this->manufacturerOrganization;
  }

  /**
   * Assigne classCode à MANU
   *
   * @return void
   */
  function setClassCode() {
    $roleClass = new CCDARoleClassManufacturedProduct();
    $roleClass->setData("MANU");
    $this->classCode = $roleClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDARoleClassManufacturedProduct
   */
  function getClassCode() {
    return $this->classCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]                   = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]                       = "CCDAII xml|element";
    $props["manufacturedLabeledDrug"]  = "CCDAPOCD_MT000040_LabeledDrug xml|element required";
    $props["manufacturedMaterial"]     = "CCDAPOCD_MT000040_Material xml|element required";
    $props["manufacturerOrganization"] = "CCDAPOCD_MT000040_Organization xml|element max|1";
    $props["classCode"]                = "CCDARoleClassManufacturedProduct xml|attribute fixed|MANU";
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
     * Test avec un manufacturedLabeledDrug correct
     */

    $labelDrug = new CCDAPOCD_MT000040_LabeledDrug();
    $labelDrug->setClassCode();
    $this->setManufacturedLabeledDrug($labelDrug);
    $tabTest[] = $this->sample("Test avec un manufacturedLabeledDrug correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un id incorrect
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTTEST");
    $this->appendId($ii);
    $tabTest[] = $this->sample("Test avec un id incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un id correct
     */

    $ii->setRoot("1.2.5");
    $this->resetListId();
    $this->appendId($ii);
    $tabTest[] = $this->sample("Test avec un id correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un manufacturerOrganization correct
     */

    $org = new CCDAPOCD_MT000040_Organization();
    $org->setClassCode();
    $this->setManufacturerOrganization($org);
    $tabTest[] = $this->sample("Test avec un manufacturerOrganization correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un manufacturerOrganization correct
     */

    $mat = new CCDAPOCD_MT000040_Material();
    $mat->setClassCode();
    $this->setManufacturedMaterial($mat);
    $tabTest[] = $this->sample("Test avec un manufacturerOrganization correct, séquence invalide", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un manufacturerOrganization correct
     */

    $this->manufacturedMaterial = null;
    $tabTest[] = $this->sample("Test avec un manufacturerOrganization correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}