<?php

/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectationUserService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\Personnel\Services\PersonnelService;

/**
 * Legacy controller for personnel module
 */
class PersonnelLegacyController extends CLegacyController
{
    /**
     * @return void
     * @throws Exception
     */
    public function httpreq_do_personnels_autocomplete(): void
    {
        $this->checkPermEdit();

        $keywords              = CView::post("_view", "str");
        $use_personnel_affecte = CView::post("use_personnel_affecte", "bool default|0");
        $service_id            = CView::post("service_id", "ref class|CService");

        CView::checkin();

        $ljoin = [];
        $where = [];

        $group                        = CGroups::loadCurrent();
        $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

        // Inclusion des fonctions secondaires
        $ljoin["secondary_function"]    = "secondary_function.user_id = users_mediboard.user_id";
        $ljoin[]                        = "functions_mediboard AS sec_fnc_mb ON " .
            "sec_fnc_mb.function_id = secondary_function.function_id";
        $where[]                        = "functions_mediboard.group_id = '$group->_id' " .
            "OR sec_fnc_mb.group_id = '$group->_id'";
        $where["users_mediboard.actif"] = "='1'";

        if ($use_personnel_affecte && $service_id) {
            $affectations_user                = CAffectationUserService::listUsersService($service_id);
            $users_ids                        = CMbArray::pluck($affectations_user, "_ref_user", "user_id");
            $where["users_mediboard.user_id"] = CSQLDataSource::prepareIn($users_ids);
        }

        $limit = $keywords ? 150 : 50;

        $user       = new CMediusers();
        $matches    = $user->seek($keywords, $where, $limit, false, $ljoin, null, "users_mediboard.user_id");
        $order_view = CMbArray::pluck($matches, "_view");
        array_multisort($order_view, SORT_ASC, $matches);

        $this->renderSmarty(
            'httpreq_do_personnels_autocomplete',
            [
                "keywords" => $keywords,
                "matches" => $matches,
                "nodebug" => true,
            ]
        );
    }

    /**
     * Autocomplete personnel by emplacement
     * @return void
     * @throws Exception
     */
    public function autocompletePersonnel(): void
    {
        $this->checkPermEdit();

        $keywords    = CView::get("autocomplete_brancardier", "str");
        $emplacement = CView::get("emplacement", "enum list|" . implode('|', CPersonnel::$_types));

        CView::checkin();

        $personnel = new PersonnelService();

        $matches    = $personnel->getUserPersonnelByEmplacementAutocomplete($emplacement, $keywords);
        $order_view = CMbArray::pluck($matches, "_view");
        array_multisort($order_view, SORT_ASC, $matches);

        $this->renderSmarty(
            "inc_field_autocomplete.tpl",
            [
                'matches'  => $matches,
                'keywords' => $keywords,
                'nodebug'  => true,
            ]
        );
    }
}
