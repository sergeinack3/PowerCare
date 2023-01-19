<?php

/**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ucum;

use Ox\Core\CAppUI;
use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupUcum extends CSetup
{
    /**
     * @inheritDoc
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "ucum";
        $this->makeRevision("0.0");

        $this->makeRevision("0.01");

        $this->setModuleCategory("autre", "autre");
        $this->addQuery('
        INSERT INTO source_http
        (name, host, loggable, role)
        VALUES 
               ("Ucum", "https://ucum.nlm.nih.gov/ucum-service/v1", "0", "'.CAppUI::conf('instance_role').'"),
               ("UcumSearch", "https://clinicaltables.nlm.nih.gov/api/ucum/v3/search", "0", "'.CAppUI::conf('instance_role').'")
               ;
        ');


        $this->mod_version = "0.02";
    }
}
