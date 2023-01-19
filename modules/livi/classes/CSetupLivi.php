<?php

/**
 * @package Mediboard\Livi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Livi;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupLivi extends CSetup
{
    /**
     * @inheritDoc
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "livi";
        $this->makeRevision("0.0");
        $this->setModuleCategory("dossier_patient", "metier");

        $this->mod_version = "0.01";
    }
}
