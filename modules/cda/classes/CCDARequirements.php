<?php

/**
 * @package Mediboard\cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\Module\Requirements\CRequirementsManager;

class CCDARequirements extends CRequirementsManager
{
    /**
     * @tab   modules
     * @group active modules
     *
     * @return void
     */
    public function checkModulesActived(): void
    {
        $modules = [
            "xds",
            "sas",
            "cda",
        ];
        $this->assertModulesActived($modules);
    }

    /**
     * @tab   Modules
     * @group instance
     *
     * @return void
     * @throws Exception
     */
    public function checkModuleConf(): void
    {
        // system
        $this->assertConfNotNull('mb_oid');
    }

    /**
     * @tab   CDA
     *
     * @return void
     * @throws Exception
     */
    public function checkMCDA(): void
    {
        // identification establishment
        $field = $this->establishment->siret ? "siret" : "finess";
        $this->assertObjectFieldNotNull($this->establishment, $field, CAppUI::tr('Identification_group(FINESS/SIRET)'));

        $ghostscript       = false;
        $path_ghostscript  = CAppUI::conf("cda path_ghostscript");
        $processorInstance = proc_open("$path_ghostscript --version", [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $processorResult   = stream_get_contents($pipes[1]);
        $processorErrors   = stream_get_contents($pipes[2]);
        proc_close($processorInstance);
        if ($processorResult) {
            $ghostscript = true;
        }
        $this->assertTrue($ghostscript, CAppUI::tr('Install_ghostscript'));
    }
}

