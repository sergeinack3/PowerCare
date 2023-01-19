<?php
/**
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Sip;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupSip extends CSetup {
  
  function __construct() {
    parent::__construct();
    
    $this->mod_name = "sip";
    $this->makeRevision("0.0");
      
    $this->makeRevision("0.11");
      
    // Déplacement des requêtes dans le module H'XML     
    $this->addDependency("webservices", "0.16");

    $this->makeRevision("0.23.1");
    $this->setModuleCategory("interoperabilite", "echange");
    
    $this->mod_version = "0.23.2";
  }
}
