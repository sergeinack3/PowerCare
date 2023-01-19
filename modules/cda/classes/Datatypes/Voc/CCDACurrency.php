<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Voc;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Voc;

/**
 * vocSet: D17388 (C-0-D17388-cpt)
 */
class CCDACurrency extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'ARS',
    'AUD',
    'BRL',
    'CAD',
    'CHF',
    'CLF',
    'CNY',
    'DEM',
    'ESP',
    'EUR',
    'FIM',
    'FRF',
    'GBP',
    'ILS',
    'INR',
    'JPY',
    'KRW',
    'MXN',
    'NLG',
    'NZD',
    'PHP',
    'RUR',
    'THB',
    'TRL',
    'TWD',
    'USD',
    'ZAR',
  );
  public $_union = array (
  );


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    parent::getProps();
    $props["data"] = "str xml|data enum|".implode("|", $this->getEnumeration(true));
    return $props;
  }
}