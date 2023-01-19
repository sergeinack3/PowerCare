<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig["search"] = [
    "CConfigEtab"               => [
        "active_indexing"       => "0",
        "active_handler_search" => "0",
        "active_search_history" => "1",
    ],
    "nb_replicas"               => "1",
    "interval_indexing"         => "100",
    "history_purge_probability" => "100",
    "history_purge_day"         => "14",
    "obfuscation_body"          => "0",
];
