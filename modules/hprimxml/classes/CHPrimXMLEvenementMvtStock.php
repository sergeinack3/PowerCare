<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;

/**
 * Class CHPrimXMLEvenementMvtStock
 */
class CHPrimXMLEvenementMvtStock extends CHPrimXMLEvenements {
  /**
   * @see parent::__construct
   */
  function __construct() {
    $this->evenement = "evt_mvtStock";
    
    $version = str_replace(".", "", CAppUI::conf('hprimxml evt_mvtStock version'));
    parent::__construct("mvtStock", "msgEvenementsMvtStocks$version");
  }
}
