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
use Ox\Mediboard\Patients\CCommunesSearch;
use Ox\Mediboard\Patients\CPaysInsee;

/**
 * INSEE Code Controller for the new management in the patient file
 */
class CCodeInseeController extends CLegacyController
{
    private const MAX_LIMIT = 15;

    /**
     * Autocomplete for INSEE Code Field
     *
     * @return void
     * @throws Exception
     */
    public function autocompleteCodeInsee(): void
    {
        $field_name = CView::request('field_name', 'str default|_code_insee');
        $keywords   = CView::request($field_name, "str");

        CView::checkin();

        $pays     = $this->searchInseePays($keywords);
        $communes = $this->searchInseeCommune($keywords);
        $matches  = array_merge($pays, $communes);

        $this->renderSmarty(
            'autocomplete_code_insee',
            [
                'keyword' => $keywords,
                'matches' => $matches,
                'nodebug' => true,
            ]
        );
    }

    /**
     * Search INSEE country by INSEE code
     *
     * @param string $keyword
     *
     * @return array
     * @throws Exception
     */
    public function searchInseePays(string $keyword, $max_limit = self::MAX_LIMIT): array
    {
        return (new CPaysInsee())->match($keyword, $max_limit);
    }

    /**
     * Search INSEE commune by INSEE code
     *
     * @param string $keyword
     *
     * @return array
     */
    public function searchInseeCommune(string $keyword, $max_limit = self::MAX_LIMIT): array
    {
        return (new CCommunesSearch())->match($keyword, CCommunesSearch::COLUMN_INSEE_COMMUNE, $max_limit);
    }
}
