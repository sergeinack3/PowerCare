<?php
/**
 * @package Mediboard\Sa
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Sa;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupSa extends CSetup {
  
  function __construct() {
    parent::__construct();
    
    $this->mod_name = "sa";
    $this->makeRevision("0.0");

    $this->makeRevision("0.01");
    $this->addDefaultConfig("sa CSa trigger_sejour"             , "sa trigger_sejour");
    $this->addDefaultConfig("sa CSa trigger_operation"          , "sa trigger_operation");
    $this->addDefaultConfig("sa CSa trigger_consultation"       , "sa trigger_consultation");
    $this->addDefaultConfig("sa CSa send_actes_consult"         , "sa send_actes_consult");
    $this->addDefaultConfig("sa CSa send_actes_interv"          , "sa send_actes_interv");
    $this->addDefaultConfig("sa CSa send_only_with_ipp_nda"     , "sa send_only_with_ipp_nda");
    $this->addDefaultConfig("sa CSa send_only_with_type"        , "sa send_only_with_type");
    $this->addDefaultConfig("sa CSa send_diags_with_actes"      , "sa send_diags_with_actes");
    $this->addDefaultConfig("sa CSa facture_codable_with_sejour", "sa facture_codable_with_sejour");
    $this->addDefaultConfig("sa CSa send_rhs"                   , "sa send_rhs");

    $this->makeRevision("0.02");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->mod_version = "0.03";
  }
}
