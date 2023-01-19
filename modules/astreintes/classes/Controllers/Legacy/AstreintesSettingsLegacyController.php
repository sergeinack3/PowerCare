<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Astreintes\CCategorieAstreinte;
use Ox\Mediboard\Etablissement\CGroups;

class AstreintesSettingsLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function listCategorieAstreintes(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty("vw_list_categories", [
            "categories" => CCategorieAstreinte::loadListCategories(),
        ]);
    }

    /**
     * @throws Exception
     */
    public function editCategorie(): void
    {
        $this->checkPermRead();

        $category_id = CView::get("category_id", "ref class|CCategorieAstreinte");

        CView::checkin();

        $this->renderSmarty("inc_edit_category", [
            "category" => $category_id ? CCategorieAstreinte::findOrFail($category_id) : new CCategorieAstreinte(),
            "groups"   => CGroups::loadGroups(),
        ]);
    }
}
