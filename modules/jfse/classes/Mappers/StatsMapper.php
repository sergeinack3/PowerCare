<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTime;
use DateTimeImmutable;
use Exception;
use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Domain\Stats\StatRequest;
use Ox\Mediboard\Jfse\Domain\Stats\StatResult;

class StatsMapper extends AbstractMapper
{
    /**
     * @throws Exception
     */
    public function arrayToStatResults(array $data): StatResult
    {
        $data = array_merge(...$data["lstResultatsStatistiques"]);

        $date_last_transmission = null;
        if (isset($data["dateDerniereTeletrans"]) && $data["dateDerniereTeletrans"] !== "00000000") {
            $date_last_transmission = new DateTimeImmutable($data["dateDerniereTeletrans"]);
        }

        return StatResult::hydrate(
            [
                "amount_days_last_transmission"        => CMbArray::get($data, "nbJDernieresTeletrans"),
                "date_last_transmission"               => $date_last_transmission,
                "amount_invoices_pending_transmission" => CMbArray::get($data, "nbFacturesAttenteTeletrans"),
                "total_invoices_rejected"              => CMbArray::get($data, "montantTotalFacturesRejetees"),
                "amount_invoices"                      => CMbArray::get($data, "nbFacturesEntreLes2Dates"),
                "total_invoices"                       => CMbArray::get($data, "montantTotalEntreLes2Dates"),
            ]
        );
    }

    /**
     * @param StatRequest[] $stat_requests
     */
    public function statsRequestsToArray(array $stat_requests): array
    {
        $stats = [];

        foreach ($stat_requests as $_stat) {
            $request = [
                "choix" => $_stat->getChoice(),
            ];

            if ($_stat->getBegin()) {
                $request["dateDebut"] = $_stat->getBegin()->format('Ymd');
            }
            if ($_stat->getEnd()) {
                $request["dateFin"] = $_stat->getEnd()->format('Ymd');
            }

            $stats[] = $request;
        }

        return [
            "getStatistiques" => [
                "lstStatistiques" => array_values($stats),
            ],
        ];
    }
}
