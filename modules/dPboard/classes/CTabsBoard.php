<?php

/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board;

use Exception;
use Ox\Core\Module\CModule;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * @codeCoverageIgnore
 */
class CTabsBoard extends AbstractTabsRegister
{
    /**
     * @throws Exception
     */
    public function registerAll(): void
    {
        $this->registerFile("viewMonth", TAB_READ);
        $this->registerFile("viewWeek", TAB_READ);
        $this->registerFile("viewDay", TAB_READ);
        $this->registerFile("viewHospitalisation", TAB_READ);

        if (CModule::getActive("dPprescription")) {
            $this->registerFile("viewPrescriptionReport", TAB_READ);
            $this->registerFile("viewTransmissionReport", TAB_READ);
        }


        $this->registerFile("viewCodingReport", TAB_READ);
        $this->registerFile("viewInterventionsNonCotees", TAB_EDIT);

        if (CModule::getActive("search")) {
            $this->registerFile("viewSearch", TAB_READ);
        }

        $this->registerFile("viewStats", TAB_READ);
        $this->registerFile("viewExamComp", TAB_READ);

        if (CMediusers::get()->isSecretaire()) {
            $this->registerFile("tdbSecretaire", TAB_READ);
        }
    }
}
