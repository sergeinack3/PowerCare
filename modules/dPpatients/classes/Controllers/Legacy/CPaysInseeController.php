<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPaysInsee;

/**
 * INSEE Pays Controller
 */
class CPaysInseeController extends CLegacyController
{
    private const MAX_LIMIT = 15;

    /**
     * Autocomplete for INSEE Pays Field
     *
     * @return void
     * @throws Exception
     */
    public function autocompletePaysInsee(): void
    {
        $this->checkPermRead();

        $field_pays         = CView::get("fieldpays", "str");
        $field_numeric_pays = CView::get("fieldnumericpays", "str");

        $pays         = $field_pays ? CView::get($field_pays, "str") : null;
        $numeric_pays = $field_numeric_pays ? CView::get($field_numeric_pays, "numchar") : null;

        CView::checkin();

        $insee_pays = new CPaysInsee();
        $ds         = $insee_pays->getDS();
        $where      = [];

        if (!$field_pays && !$field_numeric_pays) {
            return;
        }

        if ($pays) {
            $where = ["nom_fr" => $ds->prepareLike("$pays%")];
        }

        if ($numeric_pays) {
            $where = ["numerique" => $ds->prepareLike("%$numeric_pays%")];
        }

        $matches = $insee_pays->loadList($where, "nom_fr, numerique", self::MAX_LIMIT);

        $this->renderSmarty(
            'autocomplete_pays_insee',
            [
                'pays'    => $pays,
                'numPays' => $numeric_pays,
                'matches' => $matches,
                'nodebug' => false,
            ]
        );
    }
}
