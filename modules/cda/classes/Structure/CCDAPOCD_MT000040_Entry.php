<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDA_base_bl;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_ActRelationshipEntry;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;
/**
 * POCD_MT000040_Entry Class
 */
class CCDAPOCD_MT000040_Entry extends CCDARIMActRelationship {


  /**
   * @var CCDAPOCD_MT000040_Act
   */
  public $act;

  /**
   * @var CCDAPOCD_MT000040_Encounter
   */
  public $encounter;

  /**
   * @var CCDAPOCD_MT000040_Observation
   */
  public $observation;

  /**
   * @var CCDAPOCD_MT000040_ObservationMedia
   */
  public $observationMedia;

  /**
   * @var CCDAPOCD_MT000040_Organizer
   */
  public $organizer;

  /**
   * @var CCDAPOCD_MT000040_Procedure
   */
  public $procedure;

  /**
   * @var CCDAPOCD_MT000040_RegionOfInterest
   */
  public $regionOfInterest;

  /**
   * @var CCDAPOCD_MT000040_SubstanceAdministration
   */
  public $substanceAdministration;

  /**
   * @var CCDAPOCD_MT000040_Supply
   */
  public $supply;

  /**
   * Setter act
   *
   * @param CCDAPOCD_MT000040_Act $inst CCDAPOCD_MT000040_Act
   *
   * @return void
   */
  function setAct(CCDAPOCD_MT000040_Act $inst) {
    $this->act = $inst;
  }

  /**
   * Getter act
   *
   * @return CCDAPOCD_MT000040_Act
   */
  function getAct() {
    return $this->act;
  }

  /**
   * Setter encounter
   *
   * @param CCDAPOCD_MT000040_Encounter $inst CCDAPOCD_MT000040_Encounter
   *
   * @return void
   */
  function setEncounter(CCDAPOCD_MT000040_Encounter $inst) {
    $this->encounter = $inst;
  }

  /**
   * Getter encounter
   *
   * @return CCDAPOCD_MT000040_Encounter
   */
  function getEncounter() {
    return $this->encounter;
  }

  /**
   * Setter observation
   *
   * @param CCDAPOCD_MT000040_Observation $inst CCDAPOCD_MT000040_Observation
   *
   * @return void
   */
  function setObservation(CCDAPOCD_MT000040_Observation $inst) {
    $this->observation = $inst;
  }

  /**
   * Getter observation
   *
   * @return CCDAPOCD_MT000040_Observation
   */
  function getObservation() {
    return $this->observation;
  }

  /**
   * Setter observationMedia
   *
   * @param CCDAPOCD_MT000040_ObservationMedia $inst CCDAPOCD_MT000040_ObservationMedia
   *
   * @return void
   */
  function setObservationMedia(CCDAPOCD_MT000040_ObservationMedia $inst) {
    $this->observationMedia = $inst;
  }

  /**
   * Getter observationMedia
   *
   * @return CCDAPOCD_MT000040_ObservationMedia
   */
  function getObservationMedia() {
    return $this->observationMedia;
  }

  /**
   * Setter organizer
   *
   * @param CCDAPOCD_MT000040_Organizer $inst CCDAPOCD_MT000040_Organizer
   *
   * @return void
   */
  function setOrganizer(CCDAPOCD_MT000040_Organizer $inst) {
    $this->organizer = $inst;
  }

  /**
   * Getter organizer
   *
   * @return CCDAPOCD_MT000040_Organizer
   */
  function getOrganizer() {
    return $this->organizer;
  }

  /**
   * Setter procedure
   *
   * @param CCDAPOCD_MT000040_Procedure $inst CCDAPOCD_MT000040_Procedure
   *
   * @return void
   */
  function setProcedure(CCDAPOCD_MT000040_Procedure $inst) {
    $this->procedure = $inst;
  }

  /**
   * Getter procedure
   *
   * @return CCDAPOCD_MT000040_Procedure
   */
  function getProcedure() {
    return $this->procedure;
  }

  /**
   * Setter regionOfInterest
   *
   * @param CCDAPOCD_MT000040_RegionOfInterest $inst CCDAPOCD_MT000040_RegionOfInterest
   *
   * @return void
   */
  function setRegionOfInterest(CCDAPOCD_MT000040_RegionOfInterest $inst) {
    $this->regionOfInterest = $inst;
  }

  /**
   * Getter regionOfInterest
   *
   * @return CCDAPOCD_MT000040_RegionOfInterest
   */
  function getRegionOfInterest() {
    return $this->regionOfInterest;
  }

  /**
   * Setter substanceAdministration
   *
   * @param CCDAPOCD_MT000040_SubstanceAdministration $inst CCDAPOCD_MT000040_SubstanceAdministration
   *
   * @return void
   */
  function setSubstanceAdministration(CCDAPOCD_MT000040_SubstanceAdministration $inst) {
    $this->substanceAdministration = $inst;
  }

  /**
   * Getter substanceAdministration
   *
   * @return CCDAPOCD_MT000040_SubstanceAdministration
   */
  function getSubstanceAdministration() {
    return $this->substanceAdministration;
  }

  /**
   * Setter supply
   *
   * @param CCDAPOCD_MT000040_Supply $inst CCDAPOCD_MT000040_Supply
   *
   * @return void
   */
  function setSupply(CCDAPOCD_MT000040_Supply $inst) {
    $this->supply = $inst;
  }

  /**
   * Getter supply
   *
   * @return CCDAPOCD_MT000040_Supply
   */
  function getSupply() {
    return $this->supply;
  }

  /**
   * Setter typeCode
   *
   * @param String $inst String
   *
   * @return void
   */
  function setTypeCode($inst) {
    if (!$inst) {
      $this->typeCode = null;
      return;
    }
    $entity = new CCDAx_ActRelationshipEntry();
    $entity->setData($inst);
    $this->typeCode = $entity;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAx_ActRelationshipEntry
   */
  function getTypeCode() {
    return $this->typeCode;
  }

  /**
   * Assigne contextConductionInd � true
   *
   * @return void
   */
  function setContextConductionInd() {
    $bl = new CCDA_base_bl();
    $bl->setData("true");
    $this->contextConductionInd = $bl;
  }

  /**
   * Getter contextConductionInd
   *
   * @return CCDA_base_bl
   */
  function getContextConductionInd() {
    return $this->contextConductionInd;
  }


  /**
   * Retourne les propri�t�s
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]                  = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["act"]                     = "CCDAPOCD_MT000040_Act xml|element required";
    $props["encounter"]               = "CCDAPOCD_MT000040_Encounter xml|element required";
    $props["observation"]             = "CCDAPOCD_MT000040_Observation xml|element required";
    $props["observationMedia"]        = "CCDAPOCD_MT000040_ObservationMedia xml|element required";
    $props["organizer"]               = "CCDAPOCD_MT000040_Organizer xml|element required";
    $props["procedure"]               = "CCDAPOCD_MT000040_Procedure xml|element required";
    $props["regionOfInterest"]        = "CCDAPOCD_MT000040_RegionOfInterest xml|element required";
    $props["substanceAdministration"] = "CCDAPOCD_MT000040_SubstanceAdministration xml|element required";
    $props["supply"]                  = "CCDAPOCD_MT000040_Supply xml|element required";
    $props["typeCode"]                = "CCDAx_ActRelationshipEntry xml|attribute default|COMP";
    $props["contextConductionInd"]    = "CCDA_base_bl xml|attribute fixed|true";
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
     * Test avec un act correct
     */

    $ac = new CCDAPOCD_MT000040_Act();
    $cd = new CCDACD();
    $cd->setCode("SYNTH");
    $ac->setCode($cd);
    $ac->setClassCode("ACT");
    $ac->setMoodCode("INT");
    $this->setAct($ac);
    $tabTest[] = $this->sample("Test avec un act correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode incorrecte
     */

    $this->setTypeCode("TEST");
    $tabTest[] = $this->sample("Test avec un typeCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correcte
     */

    $this->setTypeCode("DRIV");
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un contextConductionInd correcte
     */

    $this->setContextConductionInd();
    $tabTest[] = $this->sample("Test avec un contextConductionInd correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un encounter correct
     */

    $enc = new CCDAPOCD_MT000040_Encounter();
    $enc->setClassCode("ACCM");
    $enc->setMoodCode("APT");
    $this->setEncounter($enc);
    $tabTest[] = $this->sample("Test avec un encounter correct, s�quence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un encounter correct
     */

    $this->act = null;
    $tabTest[] = $this->sample("Test avec un encounter correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une observation correct
     */

    $ob = new CCDAPOCD_MT000040_Observation();
    $ob->setClassCode("ALRT");
    $ob->setMoodCode("EVN");

    $cd = new CCDACD();
    $cd->setCode("SYNTH");
    $ob->setCode($cd);
    $this->setObservation($ob);
    $tabTest[] = $this->sample("Test avec une observation correct, s�quence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une observation correct
     */

    $this->encounter = null;
    $tabTest[] = $this->sample("Test avec une observation correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une observationMedia correct
     */

    $obM = new CCDAPOCD_MT000040_ObservationMedia();
    $obM->setClassCode("ALRT");
    $obM->setMoodCode("EVN");

    $ed = new CCDAED();
    $ed->setLanguage("TEST");
    $obM->setValue($ed);
    $this->setObservationMedia($obM);
    $tabTest[] = $this->sample("Test avec une observationMedia correct, s�quence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une observationMedia correct
     */

    $this->observation = null;
    $tabTest[] = $this->sample("Test avec une observationMedia correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un organizer correct
     */

    $org = new CCDAPOCD_MT000040_Organizer();
    $cs = new CCDACS();
    $cs->setCode("TEST");
    $org->setStatusCode($cs);
    $org->setClassCode("BATTERY");
    $org->setMoodCode("PRMS");
    $this->setOrganizer($org);
    $tabTest[] = $this->sample("Test avec un organizer correct, s�quence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un organizer correct
     */

    $this->observationMedia = null;
    $tabTest[] = $this->sample("Test avec un organizer correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une procedure correct
     */

    $proc = new CCDAPOCD_MT000040_Procedure();
    $proc->setClassCode("ACCM");
    $proc->setMoodCode("ARQ");
    $this->setProcedure($proc);
    $tabTest[] = $this->sample("Test avec une procedure correct, s�quence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une procedure correct
     */

    $this->organizer = null;
    $tabTest[] = $this->sample("Test avec une procedure correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une regionOfInterest correct
     */

    $reg = new CCDAPOCD_MT000040_RegionOfInterest();
    $val = new CCDAPOCD_MT000040_RegionOfInterest_value();
    $val->setUnsorted("true");
    $reg->appendValue($val);

    $cs = new CCDACS();
    $cs->setCode("TEST");
    $reg->setCode($cs);

    $ii = new CCDAII();
    $ii->setRoot("1.2.250.1.213.1.1.9");
    $reg->appendId($ii);
    $reg->setClassCode();
    $reg->setMoodCode();
    $this->setRegionOfInterest($reg);
    $tabTest[] = $this->sample("Test avec une regionOfInterest correct, s�quence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une regionOfInterest correct
     */

    $this->procedure = null;
    $tabTest[] = $this->sample("Test avec une regionOfInterest correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une substanceAdministration correct
     */

    $sub = new CCDAPOCD_MT000040_SubstanceAdministration();
    $consu = new CCDAPOCD_MT000040_Consumable();
    $manuProd = new CCDAPOCD_MT000040_ManufacturedProduct();
    $label = new CCDAPOCD_MT000040_LabeledDrug();
    $label->setClassCode();
    $manuProd->setManufacturedLabeledDrug($label);
    $consu->setManufacturedProduct($manuProd);
    $sub->setConsumable($consu);
    $sub->setClassCode();
    $sub->setMoodCode("INT");
    $this->setSubstanceAdministration($sub);
    $tabTest[] = $this->sample("Test avec une substanceAdministration correct, s�quence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une substanceAdministration correct
     */

    $this->regionOfInterest = null;
    $tabTest[] = $this->sample("Test avec une substanceAdministration correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un supply correct
     */

    $sup = new CCDAPOCD_MT000040_Supply();
    $sup->setClassCode();
    $sup->setMoodCode("EVN");
    $this->setSupply($sup);
    $tabTest[] = $this->sample("Test avec un supply correct, s�quence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un supply correct
     */

    $this->substanceAdministration = null;
    $tabTest[] = $this->sample("Test avec un supply correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}