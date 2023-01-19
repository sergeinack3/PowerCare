<?php
/**
 * @package Mediboard\Smp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Smp;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupSmp extends CSetup {
  
  function __construct() {
    parent::__construct();
    
    $this->mod_name = "smp";
    $this->makeRevision("0.0");

    $this->makeRevision("0.01");
    $this->setModuleCategory("interoperabilite", "echange");
      
    $this->mod_version = "0.02";
  }
}
