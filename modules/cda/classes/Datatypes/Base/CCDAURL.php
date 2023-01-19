<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A telecommunications address  specified according to
 * Internet standard RFC 1738
 * [http://www.ietf.org/rfc/rfc1738.txt]. The
 * URL specifies the protocol and the contact point defined
 * by that protocol for the resource.  Notable uses of the
 * telecommunication address data type are for telephone and
 * telefax numbers, e-mail addresses, Hypertext references,
 * FTP references, etc.
 */
class CCDAURL extends CCDAANY {

  public $value;

  /**
   * Setter value
   *
   * @param String $value String
   *
   * @return void
   */
  public function setValue($value) {
    if (!$value) {
      $this->value = null;
      return;
    }
    $url = new CCDA_base_url();
    $url->setData($value);
    $this->value = $url;
  }

  /**
   * Getter value
   *
   * @return CCDA_base_url
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["value"] = "CCDA_base_url xml|attribute";
    return $props;
  }

  /**
   * Fonction permettant de tester la validité de la classe
   *
   * @return array()
   */
  function test() {

    $tabTest = parent::test();

    /**
     * Test avec une valeur incorrecte
     */

    $this->setValue(":::$:!:");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/
    /**
     * Test avec une valeur correcte
     */

    $this->setValue("test");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
