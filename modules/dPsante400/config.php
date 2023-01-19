<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig["dPsante400"] = [
  "nb_rows"      => "5",
  "mark_row"     => "0",
  "cache_hours"  => "1",
  "prefix"       => "odbc",
  "dsn"          => "",
  "other_dsn"    => "",
  "user"         => "",
  "pass"         => "",
  "group_id"     => "",
  'fix_encoding' => '0',

  "CSejour" => [
    "sibling_hours" => 1,
  ],

  "CIncrementer" => [
    "cluster_count"    => 1,
    "cluster_position" => 0,
  ],
];
