<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAPN;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityClassRoot;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityDeterminer;
use Ox\Interop\Cda\Rim\CCDARIMEntity;

/**
 * POCD_MT000040_PlayingEntity Class
 */
class CCDAPOCD_MT000040_PlayingEntity extends CCDARIMEntity {

  /**
   * Setter code
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setCode(CCDACE $inst) {
    $this->code = $inst;
  }

  /**
   * Getter code
   *
   * @return CCDACE
   */
  function getCode() {
    return $this->code;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPQ $inst CCDAPQ
   *
   * @return void
   */
  function appendQuantity(CCDAPQ $inst) {
    array_push($this->quantity, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListQuantity() {
    $this->quantity = array();
  }

  /**
   * Getter quantity
   *
   * @return CCDAPQ[]
   */
  function getQuantity() {
    return $this->quantity;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPN $inst CCDAPN
   *
   * @return void
   */
  function appendName(CCDAPN $inst) {
    array_push($this->name, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListName() {
    $this->name = array();
  }

  /**
   * Getter name
   *
   * @return CCDAPN[]
   */
  function getName() {
    return $this->name;
  }

  /**
   * Setter desc
   *
   * @param CCDAED $inst CCDAED
   *
   * @return void
   */
  function setDesc(CCDAED $inst) {
    $this->desc = $inst;
  }

  /**
   * Getter desc
   *
   * @return CCDAED
   */
  function getDesc() {
    return $this->desc;
  }

  /**
   * Setter classCode
   *
   * @param String $inst String
   *
   * @return void
   */
  function setClassCode($inst) {
    if (!$inst) {
      $this->classCode = null;
      return;
    }
    $ent = new CCDAEntityClassRoot();
    $ent->setData($inst);
    $this->classCode = $ent;
  }

  /**
   * Getter classCode
   *
   * @return CCDAEntityClassRoot
   */
  function getClassCode() {
    return $this->classCode;
  }

  /**
   * Assigne determinerCode à INSTANCE
   *
   * @return void
   */
  function setDeterminerCode() {
    $determiner = new CCDAEntityDeterminer();
    $determiner->setData("INSTANCE");
    $this->determinerCode = $determiner;
  }

  /**
   * Getter determinerCode
   *
   * @return CCDAEntityDeterminer
   */
  function getDeterminerCode() {
    return $this->determinerCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]         = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["code"]           = "CCDACE xml|element max|1";
    $props["quantity"]       = "CCDAPQ xml|element";
    $props["name"]           = "CCDAPN xml|element";
    $props["desc"]           = "CCDAED xml|element max|1";
    $props["classCode"]      = "CCDAEntityClassRoot xml|attribute default|ENT";
    $props["determinerCode"] = "CCDAEntityDeterminer xml|attribute fixed|INSTANCE";
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

    $tabTest[] = $this->sample("Test avec les valeurs null", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeId correct
     */

    $this->setTypeId();
    $tabTest[] = $this->sample("Test avec un typeId correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un templateId incorrect
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTTEST");
    $this->appendTemplateId($ii);
    $tabTest[] = $this->sample("Test avec un templateId incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un templateId correct
     */

    $ii->setRoot("1.2.250.1.213.1.1.1.1");
    $this->resetListTemplateId();
    $this->appendTemplateId($ii);
    $tabTest[] = $this->sample("Test avec un templateId correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un code incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un code incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un code correct
     */

    $ce->setCode("TEST");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un code correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un name incorrect
     */

    $pn = new CCDAPN();
    $pn->setUse(array("TESTTEST"));
    $this->appendName($pn);
    $tabTest[] = $this->sample("Test avec un name incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un name correct
     */

    $pn->setUse(array("C"));
    $this->resetListName();
    $this->appendName($pn);
    $tabTest[] = $this->sample("Test avec un name correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un desc incorrect
     */

    $ed = new CCDAED();
    $ed->setLanguage(" ");
    $this->setDesc($ed);
    $tabTest[] = $this->sample("Test avec un desc incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un desc correct
     */

    $ed->setLanguage("FR");
    $this->setDesc($ed);
    $tabTest[] = $this->sample("Test avec un desc correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un determinerCode correct
     */

    $this->setDeterminerCode();
    $tabTest[] = $this->sample("Test avec un determinerCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode incorrect
     */

    $this->setClassCode("TESTTEST");
    $tabTest[] = $this->sample("Test avec un classCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode("HCE");
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un quantity incorrect
     */

    $pq = new CCDAPQ();
    $pq->setUnit(" ");
    $this->appendQuantity($pq);
    $tabTest[] = $this->sample("Test avec un quantity incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un quantity correct
     */

    $pq->setUnit("TESTTEST");
    $this->resetListQuantity();
    $this->appendQuantity($pq);
    $tabTest[] = $this->sample("Test avec un quantity correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
