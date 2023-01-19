<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admissions;
use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupAdmissions extends CSetup {

  /** Constructor */
  function __construct() {
    parent::__construct();

    $this->mod_name = "dPadmissions";

    $this->makeRevision("0.0");

    $this->makeRevision('0.1');
    $this->addDependency("dPhospi", "0.15");
    $this->addPrefQuery('show_dh_admissions', '1');

    $this->makeRevision('0.2');
    $this->addDefaultConfig('dPadmissions sortie show_prestations_sorties', 'dPadmissions show_prestations_sorties');
    $this->addDefaultConfig('dPadmissions General hour_matin_soir', 'dPadmissions hour_matin_soir');
    $this->addDefaultConfig('dPadmissions General show_curr_affectation', 'dPadmissions show_curr_affectation');
    $this->addDefaultConfig('dPadmissions General show_deficience', 'dPadmissions show_deficience');
    $this->addDefaultConfig('dPadmissions automatic_reload auto_refresh_frequency_identito', 'dPadmissions auto_refresh_frequency_identito');
    $this->addDefaultConfig('dPadmissions automatic_reload auto_refresh_frequency_admissions', 'dPadmissions auto_refresh_frequency_admissions');
    $this->addDefaultConfig('dPadmissions automatic_reload auto_refresh_frequency_sorties', 'dPadmissions auto_refresh_frequency_sorties');
    $this->addDefaultConfig('dPadmissions automatic_reload auto_refresh_frequency_preadmissions', 'dPadmissions auto_refresh_frequency_preadmissions');
    $this->addDefaultConfig('dPadmissions automatic_reload auto_refresh_frequency_permissions', 'dPadmissions auto_refresh_frequency_permissions');
    $this->addDefaultConfig('dPadmissions automatic_reload auto_refresh_frequency_presents', 'dPadmissions auto_refresh_frequency_presents');

    $this->makeRevision("0.3");
    $this->setModuleCategory("circuit_patient", "metier");

    $this->mod_version = "0.4";

  }
}
