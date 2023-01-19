<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Board\TableauDeBordSecretaire;

class TdbSecretaireLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function tdbSecretaire(): void
    {
        $this->checkPermRead();

        $chir_ids = CView::get("chir_ids", 'str', true);
        $function_id = CView::get("function_id", "ref class|CFunctions");
        $nbPreviousDays = "-".CAppUI::pref("nb_previous_days")." days";
        $date_min = CView::get('date_min', ['date', 'default' => CMbDT::date($nbPreviousDays)]);

        CView::checkin();

        $tdb = new TableauDeBordSecretaire();

        $tdb->loadPraticiensTdb($chir_ids);
        $tdb->loadFunctionTdb($function_id);

        $praticiens = $tdb->getPraticiens();
        $function   = $tdb->getFunction();

        $this->renderSmarty(
            'vw_tdb_secretaire',
            [
                'praticiens' => $praticiens,
                'date_min'   => $date_min,
                'function'   => $function,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function getListDocuments(): void
    {
        $this->checkPermRead();

        $chir_ids = CView::get("chir_ids", 'str');
        $function_id = CView::get("function_id", "ref class|CFunctions");
        $nbPreviousDays = "-".CAppUI::pref("nb_previous_days")." days";
        $date_min = CView::get('date_min', ['date', 'default' => CMbDT::date($nbPreviousDays)]);

        CView::checkin();
        CView::enforceSlave();

        $tdb = new TableauDeBordSecretaire();
        $tdb->loadChirsDocumentsFromDate($chir_ids, $date_min, $function_id);

        $affichageDocs = $tdb->getDocumentsByStatus();
        $total         = $tdb->getTotalDocumentsByStatus();

        $this->renderSmarty(
            'inc_tdb_secretaire',
            [
                "affichageDocs" => $affichageDocs,
                "total"         => $total,
            ]
        );
    }
}
