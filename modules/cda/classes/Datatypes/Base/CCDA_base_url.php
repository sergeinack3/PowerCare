<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Base;

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
class CCDA_base_url extends CCDA_Datatype_Base {

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["data"] = "uri xml|data";
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
     * Test avec une valeur null
     */

    $tabTest[] = $this->sample("Test avec une valeur null", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur incorrecte
     */

    $this->setData(":::$:!:");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur correcte
     */

    $this->setData("test");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
