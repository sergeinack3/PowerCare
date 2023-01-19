<?php

/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Exception;
use Ox\Core\CRequest;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CSetupMonitoringPatient extends CSetup
{
    /**
     * @return bool
     * @throws Exception
     */
    protected function addMonitoringPatientPerm(): bool
    {
        $patient = CModule::getActive("dPpatients");

        if (!$patient) {
            return true;
        }

        $ds = CSQLDataSource::get("std");

        $request = new CRequest();
        $request->addSelect("mod_id");
        $request->addTable("modules");
        $request->addWhere(["mod_name" => "= 'monitoringPatient'"]);

        $mod_id_monitoring_patient = $ds->loadResult($request->makeSelect());

        if (!$mod_id_monitoring_patient) {
            return true;
        }

        $mod_id_patient = $patient->_id;

        $query = "INSERT INTO `perm_module` (`user_id`, `mod_id`, `permission`, `view`)
                SELECT `user_id`, '$mod_id_monitoring_patient', `permission`, `view`
                FROM `perm_module`
                WHERE `mod_id` = '$mod_id_patient'";

        $ds->exec($query);

        return true;
    }

    /**
     * @see parent::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "monitoringPatient";
        $this->makeRevision("0.0");

        $this->makeRevision("0.01");

        $this->setModuleCategory("parametrage", "metier");

        if (CModule::getActive("dPpatients")) {
            $this->addMethod("addMonitoringPatientPerm");
        }

        $this->makeRevision('0.02');

        $this->addDependency('observationResult', '0.4');

        $this->makeRevision("0.03");
        $query = "ALTER TABLE `supervision_graph`
                CHANGE `automatic_protocol` `automatic_protocol` ENUM ('Kheops-Concentrator', 'MD-Stream');";
        $this->addQuery($query, true);

        $this->mod_version = "0.04";
    }
}
