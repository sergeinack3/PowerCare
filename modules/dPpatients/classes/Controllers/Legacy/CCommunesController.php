<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CCommunesSearch;

class CCommunesController extends CLegacyController
{
    public function autocomplete_cp_commune(): void
    {
        $column  = CView::get("column", "enum list|" . implode('|', CCommunesSearch::DEFAULT_SEARCH_COLUMNS));
        $max     = (int) CView::get("max", "num default|30");
        $name    = CView::get("name_input", "str");
        $keyword = CView::post($name, "str");

        if (!in_array($column, CCommunesSearch::DEFAULT_SEARCH_COLUMNS)) {
            trigger_error("Column '$column' is invalid");

            return;
        }

        CView::checkin();
        CView::enableSlave();

        $communes_search = new CCommunesSearch();
        $matches = $communes_search->match($keyword, $column, $max);

        $this->renderSmarty(
            'autocomplete_cp_commune',
            [
                'keyword' => $keyword,
                'matches' => $matches,
                'nodebug' => true,
            ]
        );
    }
}
