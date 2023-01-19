<?php

/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stats\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Hospi\CPrestationPonctuelle;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Description
 */
class CStatsLegacyController extends CLegacyController
{
    public function vw_prestations(): void
    {
        $this->checkPermRead();

        $filter = new CSejour();

        $filter->_date_min = CView::get("_date_min", "str default|" . CMbDT::date("-3 MONTHS"));

        $filter->_date_max = CView::get("_date_max", "str default|" . CMbDT::date());

        $filter->_service     = CView::get("service_id", "ref class|CService");
        $filter->type         = CView::get("type", "str default|1");
        $filter->praticien_id = CView::get("prat_id", "ref class|CMediusers");
        $filter->_specialite  = CView::get("discipline_id", "num");
        $filter->septique     = CView::get("septique", "bool");

        CView::checkin();

        $user      = new CMediusers();
        $listPrats = $user->loadPraticiens(PERM_READ);

        $service            = new CService();
        $where              = [];
        $where["cancelled"] = "= '0'";
        $listServices       = $service->loadGroupList($where);

        $listDisciplines = new CDiscipline();
        $listDisciplines = $listDisciplines->loadUsedDisciplines();

        $this->renderSmarty(
            "vw_prestations",
            [
                "filter"          => $filter,
                "listPrats"       => $listPrats,
                "listServices"    => $listServices,
                "listDisciplines" => $listDisciplines,
            ]
        );
    }

    public function ajax_stats_prestations(): void
    {
        $date_min     = CView::get("_date_min", "str default|" . CMbDT::date("-3 MONTHS"));
        $date_max     = CView::get("_date_max", "str default|" . CMbDT::date());
        $service_id   = CView::get("service_id", "ref class|CService");
        $type         = CView::get("type", "str default|1");
        $praticien_id = CView::get("prat_id", "ref class|CMediusers");
        $specialite   = CView::get("discipline_id", "num");
        $septique     = CView::get("septique", "bool");

        CView::checkin();

        $ds    = CSQLDataSource::get('std');
        $where = [
            "entree_reelle" => $ds->prepare("> ?", $date_min),
            "sortie_reelle" => $ds->prepare("< ?", $date_max),
        ];

        if ($type === "1") {
            $where["type"] = $ds->prepareIn(["comp", "ambu"]);
        } elseif ($type !== "") {
            $where["type"] = $ds->prepare("= %", $type);
        }
        if ($service_id) {
            $where["service_id"] = $ds->prepare("= %", $service_id);
        }
        if ($praticien_id) {
            $where["praticien_id"] = $ds->prepare("= %", $praticien_id);
        }
        if ($septique) {
            $where["septique"] = $ds->prepare("= %", $septique);
        }
        if ($specialite) {
            $where_specialite      = [
                "users_mediboard.spec_cpam_id" => $ds->prepare("= ?", $specialite),
            ];
            $prats_specialite      = (new CMediusers())->loadIds($where_specialite);
            $where["praticien_id"] = $ds->prepareIn($prats_specialite);
        }

        $intervals = [];
        $date_min  = CMbDT::date("first day of this month", $date_min);
        $date_max  = CMbDT::date("last day of this month", $date_max);
        $date      = $date_min;
        while ($date < $date_max) {
            $intervals[] = CMbDT::format($date, "%m/%Y");
            $date        = CMbDT::date("+1 MONTH", $date);
        }
        $items_prestations = (new CItemPrestation())->loadList(["actif" => "= '1'"]);
        $stats_prestations = [];
        $sejours           = (new CSejour())->loadList($where);
        foreach ($items_prestations as $id => $item) {
            // On crée un tableau pour chaque item de prestation
            $stats_prestations[$id] = [];
            // On crée un tableau pour chaque intervalle
            foreach ($intervals as $interval) {
                // On crée un tableau contenant le souhaité et le réalisé
                $stats_prestations[$id][$interval] = ["souhait" => 0, "reel" => 0];
            }
        }
        foreach ($sejours as $sejour) {
            $sejour->getPrestationsForStats();
            if (count($sejour->_ref_prestations)) {
                foreach ($sejour->_ref_prestations as $date => $prestas) {
                    foreach ($prestas as $item_presta) {
                        $month = CMbDT::format($item_presta['liaison']->date, "%m/%Y");
                        // On ajoute la quantité
                        if (array_key_exists('souhait', $item_presta)) {
                            // Prestation journalière
                            $stats_prestations[$item_presta['souhait']->_id][$month]["souhait"] += intval(
                                $item_presta['quantite']
                            );
                            if ($item_presta['realise']->_id) {
                                $stats_prestations[$item_presta['realise']->_id][$month]["reel"] +=
                                    intval($item_presta['quantite']);
                            }
                        } else {
                            // Prestation ponctuelle
                            $stats_prestations[$item_presta['item']->_id][$month]["souhait"] += intval(
                                $item_presta['quantite']
                            );
                            $stats_prestations[$item_presta['item']->_id][$month]["reel"]    += intval(
                                $item_presta['quantite']
                            );
                        }
                    }
                }
            }
        }
        $prestas      = [
            "CPrestationJournaliere" => [],
            "CPrestationPonctuelle"  => [],
        ];
        $group_id = CGroups::loadCurrent()->_id;
        $presta_journ = (new CPrestationJournaliere())->loadList(["group_id" => $ds->prepare(" = ?", $group_id)]);
        $presta_ponc  = (new CPrestationPonctuelle())->loadList(["group_id" => $ds->prepare(" = ?", $group_id)]);
        foreach ($presta_journ as $presta) {
            $presta->loadRefsItems();
            $prestas["CPrestationJournaliere"][$presta->nom] = array_keys($presta->_ref_items);
        }
        foreach ($presta_ponc as $presta) {
            $presta->loadRefsItems();
            $prestas["CPrestationPonctuelle"][$presta->nom] = array_keys($presta->_ref_items);
        }
        $this->renderSmarty(
            "inc_stats_prestation",
            [
                "sejours"           => $sejours,
                "prestas"           => $prestas,
                "items_prestations" => $items_prestations,
                "intervals"         => $intervals,
                "stats_prestations" => $stats_prestations,
            ]
        );
    }
}
