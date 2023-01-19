<?php

/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Récupération du graphique d'affichage du nombre de nuits passées
 * par service
 *
 * @param string $debut         Date de début
 * @param string $fin           Date de fin
 * @param int    $prat_id       Identifiant du praticien
 * @param int    $service_id    Identifiant du service
 * @param int    $type_adm      Type d'admission
 * @param int    $discipline_id Identifiant de la discipline
 * @param int    $septique      Filtre sur les séjours septiques
 * @param string $type_data     Type de données (réèlle ou prévue)
 *
 * @return array
 */
function graphJoursParService(
    $debut = null,
    $fin = null,
    $prat_id = 0,
    $service_id = 0,
    $type_adm = 0,
    $func_id = 0,
    $discipline_id = 0,
    $septique = 0,
    $type_data = "prevue"
) {
    if (!$debut) {
        $debut = CMbDT::date("-1 YEAR");
    }
    if (!$fin) {
        $fin = CMbDT::date();
    }

    $prat = new CMediusers();
    $prat->load($prat_id);

    $discipline = new CDiscipline();
    $discipline->load($discipline_id);

    $ticks       = [];
    $serie_total = [
        'label'   => 'Total',
        'data'    => [],
        'markers' => ['show' => true],
        'bars'    => ['show' => false],
    ];
    for ($i = $debut; $i <= $fin; $i = CMbDT::date("+1 MONTH", $i)) {
        $ticks[]               = [count($ticks), CMbDT::transform("+0 DAY", $i, "%m/%Y")];
        $serie_total['data'][] = [count($serie_total['data']), 0];
    }

    $where = ['cancelled' => "= '0'"];
    if ($service_id) {
        $where["service_id"] = "= '$service_id'";
    }
    $service            = new CService();
    $services           = $service->loadGroupList($where);
    foreach ($services as $key => $_service) {
        if (!$_service->loadRefsLits()) {
            unset($services[$key]);
        }
    }

    $sejour     = new CSejour();
    $listHospis = [
            1 => "Hospi complètes + ambu",
        ] + $sejour->_specs["type"]->_locales;

    $total  = 0;
    $series = [];

    // Patients placés

    foreach ($services as $service) {
        $serie = [
            'data'  => [],
            'label' => $service->nom,
        ];

        $curr_month = $debut;
        $result     = [];
        while ($curr_month <= $fin) {
            $end_month = CMbDT::date("+1 MONTH", $curr_month);
            $end_month = CMbDT::date("-1 DAY", $end_month);

            $query = "SELECT
                  SUM(DATEDIFF(
                    LEAST(affectation.sortie, '$end_month 23:59:59'),
                    GREATEST(affectation.entree, '$curr_month 00:00:00')
                  )) AS total,
                  DATE_FORMAT('$curr_month', '%m/%Y') AS mois,
                  DATE_FORMAT('$curr_month', '%Y%m') AS orderitem
                FROM affectation
                LEFT JOIN sejour ON sejour.sejour_id = affectation.sejour_id
                LEFT JOIN lit ON affectation.lit_id = lit.lit_id
                LEFT JOIN chambre ON lit.chambre_id = chambre.chambre_id
                LEFT JOIN service ON chambre.service_id = service.service_id
                LEFT JOIN users_mediboard ON sejour.praticien_id = users_mediboard.user_id
                WHERE sejour.annule = '0'
                  AND affectation.sortie >= '$curr_month 00:00:00'
                  AND affectation.entree <= '$end_month 23:59:59'
                  AND service.service_id = '$service->_id'";

            if ($prat_id) {
                $query .= "\nAND sejour.praticien_id = '$prat_id'";
            }
            if ($discipline_id) {
                $query .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
            }
            if ($septique) {
                $query .= "\nAND sejour.septique = '$septique'";
            }

            if ($type_adm) {
                if ($type_adm == 1) {
                    $query .= "\nAND (sejour.type = 'comp' OR sejour.type = 'ambu')";
                } else {
                    $query .= "\nAND sejour.type = '$type_adm'";
                }
            }
            $query .= "\nGROUP BY mois ORDER BY orderitem";

            $result_month = $sejour->_spec->ds->loadlist($query);

            foreach ($result_month as $curr_result) {
                $key = $curr_result["orderitem"] . $service->_id;
                if (!isset($result[$key])) {
                    $result[$key] = $curr_result;
                } else {
                    $result[$key]["total"] += $curr_result["total"];
                }
            }

            $curr_month = CMbDT::date("+1 MONTH", $curr_month);
        }

        foreach ($ticks as $i => $tick) {
            $f = true;
            foreach ($result as $r) {
                if ($tick[1] == $r["mois"]) {
                    $serie["data"][]            = [$i, $r["total"]];
                    $serie_total["data"][$i][1] += $r["total"];
                    $total                      += $r["total"];
                    $f                          = false;
                    break;
                }
            }
            if ($f) {
                $serie["data"][] = [count($serie["data"]), 0];
            }
        }
        $series[] = $serie;
    }

    // Patients non placés

    if (!$service_id) {
        $serie    = [
            'data'  => [],
            'label' => "Non placés",
        ];
        $series[] = $serie;
    }

    $series[] = $serie_total;

    $subtitle = "$total nuits";
    if ($prat_id) {
        $subtitle .= " - Dr $prat->_view";
    }
    if ($discipline_id) {
        $subtitle .= " - $discipline->_view";
    }
    if ($type_adm) {
        $subtitle .= " - " . $listHospis[$type_adm];
    }
    if ($septique) {
        $subtitle .= " - Septiques";
    }

    $options = [
        'title'       => "Nombre de nuits par service",
        'subtitle'    => $subtitle,
        'xaxis'       => ['labelsAngle' => 45, 'ticks' => $ticks],
        'yaxis'       => ['min' => 0, 'autoscaleMargin' => 5],
        'bars'        => ['show' => true, 'stacked' => true, 'barWidth' => 0.8],
        'HtmlText'    => false,
        'legend'      => ['show' => true, 'position' => 'nw'],
        'grid'        => ['verticalLines' => false],
        'spreadsheet' => [
            'show'             => true,
            'csvFileSeparator' => ';',
            'decimalSeparator' => ',',
            'tabGraphLabel'    => 'Graphique',
            'tabDataLabel'     => 'Données',
            'toolbarDownload'  => 'Fichier CSV',
            'toolbarSelectAll' => 'Sélectionner tout le tableau',
        ],
    ];

    return ['series' => $series, 'options' => $options];
}
