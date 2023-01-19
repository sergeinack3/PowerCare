<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig["rpps"] = [
    'download_directory'           => '',
    'sync_step'                    => '50',
    'disable_days_withtout_update' => '30',
];

$dPconfig['db']['rpps_import'] = [
    'dbtype' => 'pdo_mysql',
    'dbhost' => '127.0.0.1',
    'dbname' => 'rpps',
    'dbuser' => '',
    'dbpass' => '',
];
