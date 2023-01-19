<?php

/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel\Services;

use Exception;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Les services en commun pour les contrôleurs
 */
class PersonnelService
{
    /**
     * @param string      $emplacement
     * @param string|null $keywords
     * @param string|null $group_id
     *
     * @return CUser[]
     * @throws Exception
     */
    public function getUserPersonnelByEmplacementAutocomplete(
        string $emplacement,
        string $keywords = null,
        string $group_id = null
    ): array {
        $personnel = new CUser();
        $ds        = $personnel->getDS();
        $group_id  = $group_id ?: CGroups::loadCurrent()->_id;

        $ljoin = [];
        $order = [];
        $where = [];

        $order[] = "users.user_last_name, users.user_first_name ASC";

        $where["personnel.emplacement"] = $ds->prepare("= ?", $emplacement);
        $where["personnel.actif"]       = $ds->prepare("= ?", '1');
        $where[]                        = "functions_mediboard.group_id = '$group_id' " .
            "OR secondary_function_B.group_id = '$group_id'";

        $ljoin["personnel"]           = "personnel.user_id = users.user_id";
        $ljoin["users_mediboard"]     = "users_mediboard.user_id = personnel.user_id";
        $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";
        $ljoin["secondary_function"]  = "secondary_function.user_id = users_mediboard.user_id";
        $ljoin[]                      = "functions_mediboard secondary_function_B 
        ON secondary_function_B.function_id = secondary_function.function_id";

        return $personnel->seek($keywords, $where, null, true, $ljoin, $order);
    }
}
