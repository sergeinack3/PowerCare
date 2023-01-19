<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Tests\Fixtures;

use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Pmsi\CRelancePMSI;
use Ox\Tests\Fixtures\Fixtures;

class CRelancePMSIFixtures extends Fixtures
{
    public const RELANCE_PMSI_FUNCTION = 'relance_pmsi_function';
    public const RELANCE_PMSI_1        = 'relance_pmsi_1';
    public const RELANCE_PMSI_2        = 'relance_pmsi_2';


    public function load()
    {
        $function           = new CFunctions();
        $function->group_id = CGroups::loadCurrent()->_id;
        $function->type     = "cabinet";
        $function->text     = "test relance pmsi";
        $function->color    = "00ff00";

        $this->store($function, self::RELANCE_PMSI_FUNCTION);

        $users = $this->getUsers(2, false);

        $user_1              = array_pop($users);
        $user_1->function_id = $function->_id;
        $this->store($user_1, self::RELANCE_PMSI_1);

        $user_2              = array_pop($users);
        $user_2->function_id = $function->_id;
        $this->store($user_2, self::RELANCE_PMSI_2);

        $relance_pmsi_1          = new CRelancePMSI();
        $relance_pmsi_1->chir_id = $user_1->_id;
        $this->store($relance_pmsi_1, self::RELANCE_PMSI_1);

        $relance_pmsi_2          = new CRelancePMSI();
        $relance_pmsi_2->chir_id = $user_2->_id;
        $this->store($relance_pmsi_2, self::RELANCE_PMSI_2);
    }
}
