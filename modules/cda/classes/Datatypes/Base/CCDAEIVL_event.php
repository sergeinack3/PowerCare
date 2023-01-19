<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\Voc\CCDATimingEvent;

/**
 * A code for a common (periodical) activity of daily
 * living based on which the event related periodic
 * interval is specified.
 */
class CCDAEIVL_event extends CCDACE {

  private $name = "EIVL.event";

  /**
   * Setter Code
   *
   * @param String $code String
   *
   * @return void
   */
  public function setCode($code) {
    if (!$code) {
      $this->code = null;
      return;
    }
    $cod = new CCDATimingEvent();
    $cod->setData($code);
    $this->code = $cod;
  }

  /**
   * Fixe les données
   */
  function __construct() {

    $this->setCodeSystem("2.16.840.1.113883.5.139");
    $this->setCodeSystemName("TimingEvent");
  }

  /**
   * retourne le nom du type CDA
   *
   * @return string
   */
  function getNameClass() {
    return $this->name;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["code"] = "CCDATimigEvent xml|attribute";
    $props["codeSystem"] = "CCDA_base_uid xml|attribute default|2.16.840.1.113883.5.139";
    $props["codeSystemName"] = "CCDA_base_st xml|attribute default|TimingEvent";
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
     * Test avec code incorrecte
     */

    $this->setCode(" ");

    $tabTest[] = $this->sample("Test avec un code incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec code correct
     */

    $this->setCode("ICM");

    $tabTest[] = $this->sample("Test avec un code correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
