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
use Ox\Mediboard\Board\TdbStats;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Stats\GrapheActivites;
use Ox\Mediboard\Stats\GraphePatient;

class TdbStatsLegacyController extends CLegacyController
{
    private CMediusers $user;
    private CMediusers $praticien;
    private CFunctions $function;

    private string $perm_fonct;

    /**
     * @throws Exception
     */
    public function incBoardStats(): void
    {
        $prat_selected     = CView::get("praticien_id", "ref class|CMediusers", true);
        $function_selected = CView::get("function_id", "ref class|CFunctions");

        // Chargement de l'utilisateur courant
        $user       = CMediusers::get();
        $perm_fonct = CAppUI::pref("allow_other_users_board");

        if (!$user->isProfessionnelDeSante() && !$user->isSecretaire()) {
            CAppUI::accessDenied();
        }

        $prat     = new CMediusers();
        $function = new CFunctions();

        if ($prat_selected) {
            $function_selected = null;
            $prat->load($prat_selected);
        } elseif ($user->isProfessionnelDeSante() && !$function_selected) {
            $prat = $user;
        }

        if ($function_selected) {
            $function->load($function_selected);
        }
        $this->user       = $user;
        $this->praticien  = $prat;
        $this->function   = $function;
        $this->perm_fonct = $perm_fonct;
    }

    private function renderBoard(): void
    {
        $this->renderSmarty(
            "inc_board",
            [
                "user"       => $this->user,
                "prat"       => $this->praticien,
                "function"   => $this->function,
                "perm_fonct" => $this->perm_fonct,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewStats(): void
    {
        $this->checkPermRead();

        $this->incBoardStats();

        $stat = CView::post("stat", "str default|viewSejoursInterventions");

        CView::checkin();

        $this->renderBoard();
        if (!$this->praticien->_id) {
            return;
        }

        $stats_view = (new TdbStats())->getAllStatsViews($stat);

        $this->renderSmarty("vw_stats", [
            "stats"        => $stats_view,
            "stat"         => $stat,
            "praticien_id" => $this->praticien->_id,
        ]);
    }

    /**
     * @throws Exception
     */
    public function viewTraceCotes(): void
    {
        $this->checkPermRead();

        $date_interv  = CView::get("date_interv", "date default|now");
        $praticien_id = CView::get("praticien_id", "ref class|CMediusers", true);

        CView::checkin();

        $tdb_stats = new TdbStats();

        $praticien = CMediusers::findOrFail($praticien_id);

        $listIntervs = $tdb_stats->getVerificationCotesStats($praticien, $date_interv);

        $this->renderSmarty(
            "vw_trace_cotes",
            [
                "date_interv"  => $date_interv,
                "listIntervs"  => $listIntervs,
                "praticien_id" => $praticien_id,
                "prec"         => CMbDT::date("-1 DAYS", $date_interv),
                "suiv"         => CMbDT::date("+1 DAYS", $date_interv),
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewSejoursInterventions(): void
    {
        $this->checkPermRead();

        $this->incBoardStats();

        $filterSejour    = new CSejour();
        $filterOperation = new COperation();

        $filterSejour->_date_min_stat = CView::get("_date_min_stat", "date default|" . CMbDT::date("-1 YEAR"));
        $rectif                       = CMbDT::transform("+0 DAY", $filterSejour->_date_min_stat, "%d") - 1;
        $filterSejour->_date_min_stat = CMbDT::date("-$rectif DAYS", $filterSejour->_date_min_stat);

        $filterSejour->_date_max_stat = CView::get("_date_max_stat", "date default|" . CMbDT::date());
        $rectif                       = CMbDT::transform("+0 DAY", $filterSejour->_date_max_stat, "%d") - 1;
        $filterSejour->_date_max_stat = CMbDT::date("-$rectif DAYS", $filterSejour->_date_max_stat);
        $filterSejour->_date_max_stat = CMbDT::date("+1 MONTH", $filterSejour->_date_max_stat);
        $filterSejour->_date_max_stat = CMbDT::date("-1 DAY", $filterSejour->_date_max_stat);


        $filterSejour->praticien_id   = $this->praticien->_id;
        $filterSejour->type           = CView::get("type", "str default|1");
        $filterOperation->_codes_ccam = strtoupper(CView::get("_codes_ccam", "str"));
        $refresh                      = CView::get("refresh", "bool default|0");

        CView::checkin();

        $graph_activite = new GrapheActivites(
            $filterSejour->_date_min_stat,
            $filterSejour->_date_max_stat,
            $filterSejour->praticien_id,
            0,
            0,
            0,
            $filterOperation->_codes_ccam,
            "",
            0
        );
        $graph_activite->getData();

        $graph_patient = new GraphePatient(
            $filterSejour->_date_min_stat,
            $filterSejour->_date_max_stat,
            $filterSejour->praticien_id,
            0,
            $filterSejour->type,
            0,
            0,
            'prevue',
            $filterOperation->_codes_ccam
        );
        $graph_patient->getData();

        $graphs = [
            $graph_patient->getGraphData(),
            $graph_activite->getGraphData(),
        ];

        $this->renderSmarty(
            "vw_sejours_interventions",
            [
                "filterSejour"    => $filterSejour,
                "filterOperation" => $filterOperation,
                "prat"            => $this->praticien,
                "graphs"          => $graphs,
                "refresh"         => $refresh,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewStatsConsultations(): void
    {
        $this->checkPermRead();

        $date_min = CView::get("_date_min", "date default|" . CMbDT::date("-1 YEAR"));
        $date_max = CView::get("_date_max", "date default|now");
        $prat     = CView::get("praticien_id", "ref class|CMediusers", true);
        $refresh  = CView::get("refresh", "bool default|0");

        CView::checkin();

        $praticien = CMediusers::findOrFail($prat);

        $tdbstats = new TdbStats();

        $graphs = $tdbstats->getGraphsConsultations($date_min, $date_max, $praticien);

        $filterConsultation = $tdbstats->getFilterConsultation();

        $this->renderSmarty(
            "vw_stats_consultations",
            [
                "filterConsultation" => $filterConsultation,
                "prat"               => $praticien,
                "graphs"             => $graphs,
                "refresh"            => $refresh,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewPrescripteurs(): void
    {
        $this->checkPermRead();

        $this->incBoardStats();

        $start_prescripteurs = CView::get("start_prescripteur", "bool default|0");
        $step_prescripteurs  = 20;

        CView::checkin();

        $tdbstats = new TdbStats();

        [$prescripteurs, $total_prescripteurs, $medecins,] = $tdbstats->getStatsPrescripteurs(
            $this->praticien,
            $start_prescripteurs
        );

        $this->renderBoard();

        $this->renderSmarty(
            "vw_prescripteurs",
            [
                "start_prescripteurs" => $start_prescripteurs,
                "step_prescripteurs"  => $step_prescripteurs,
                "total_prescripteurs" => $total_prescripteurs,
                "medecins"            => $medecins,
                "prescripteurs"       => $prescripteurs,
            ]
        );
    }
}
